<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\TechnicalVisit;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\PaymentStatusNotification;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function gatewayName(): string
    {
        return (string) config('payments.driver');
    }

    public function gateway(?string $name = null): PaymentGatewayInterface
    {
        $class = config('payments.gateways.'.($name ?? $this->gatewayName()));

        if (! $class) {
            throw new DomainException('Passerelle de paiement non configurée.');
        }

        return app($class);
    }

    /** Crée la transaction et retourne l'URL de paiement de l'opérateur. */
    public function start(User $user, TechnicalVisit $visit, PaymentProvider $provider): string
    {
        if (! $visit->canBePaid()) {
            throw new DomainException('Les frais de cette visite sont déjà réglés, ou la visite est annulée.');
        }

        $gateway = $this->gateway();

        $transaction = DB::transaction(function () use ($user, $visit, $provider) {
            // Une seule transaction en attente par visite : les précédentes expirent
            $visit->transactions()
                ->where('status', PaymentStatus::PENDING->value)
                ->update(['status' => PaymentStatus::EXPIRED->value]);

            return $visit->transactions()->create([
                'user_id' => $user->id,
                'provider' => $provider,
                'gateway' => $this->gatewayName(),
                'reference' => 'AS'.now()->format('ymd').'-'.Str::upper(Str::random(8)),
                'amount' => $visit->fee_amount,   // toujours le montant du serveur
                'currency' => 'XOF',
                'status' => PaymentStatus::PENDING,
            ]);
        });

        $init = $gateway->initiate($transaction);
        $transaction->update(['provider_ref' => $init['provider_ref']]);

        return $init['redirect_url'];
    }

    /**
     * Traite un webhook. Retourne : paid, failed, ignored, invalid_signature,
     * unknown_reference ou amount_mismatch.
     */
    public function handleWebhook(string $gatewayName, Request $request): string
    {
        $gateway = $this->gateway($gatewayName);

        if (! $gateway->verifySignature($request)) {
            Log::warning('Webhook paiement : signature invalide', ['gateway' => $gatewayName, 'ip' => $request->ip()]);

            return 'invalid_signature';
        }

        $event = $gateway->parseWebhook($request);
        $payload = json_decode($request->getContent(), true);
        $notify = null;

        $result = DB::transaction(function () use ($event, $payload, $gatewayName, &$notify) {
            $transaction = Transaction::where('reference', $event['reference'])
                ->where('gateway', $gatewayName)
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                return 'unknown_reference';
            }

            // Idempotence : une transaction déjà traitée n'est jamais retraitée
            if ($transaction->status !== PaymentStatus::PENDING) {
                return 'ignored';
            }

            if ($event['amount'] !== $transaction->amount) {
                Log::warning('Webhook paiement : montant différent', [
                    'reference' => $transaction->reference,
                    'attendu' => $transaction->amount,
                    'recu' => $event['amount'],
                ]);
                $transaction->update([
                    'status' => PaymentStatus::FAILED,
                    'event_id' => $event['event_id'],
                    'payload' => $payload,
                ]);

                return 'amount_mismatch';
            }

            if ($event['status'] === 'paid') {
                $transaction->update([
                    'status' => PaymentStatus::PAID,
                    'event_id' => $event['event_id'],
                    'payload' => $payload,
                    'paid_at' => now(),
                ]);
                $this->applyPaid($transaction);
                $notify = [$transaction, true];

                return 'paid';
            }

            if ($event['status'] === 'failed') {
                $transaction->update([
                    'status' => PaymentStatus::FAILED,
                    'event_id' => $event['event_id'],
                    'payload' => $payload,
                ]);
                $notify = [$transaction, false];

                return 'failed';
            }

            return 'ignored';
        });

        // Notification après validation de la transaction en base
        if ($notify) {
            [$transaction, $success] = $notify;
            $this->notifyClient($transaction, $success);
        }

        return $result;
    }

    /** Effet métier d'un paiement réussi. */
    private function applyPaid(Transaction $transaction): void
    {
        $payable = $transaction->payable;

        if ($payable instanceof TechnicalVisit) {
            $payable->update(['fee_paid_at' => now()]);
        }
    }

    private function notifyClient(Transaction $transaction, bool $success): void
    {
        $label = $transaction->description();
        $amount = $transaction->amountForHumans();

        $message = $success
            ? "Paiement de {$amount} reçu ({$label}). Merci !"
            : "Le paiement de {$amount} ({$label}) a échoué. Vous pouvez réessayer.";

        $transaction->user->notify(new PaymentStatusNotification($transaction->reference, $message));
    }
}