<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Faux opérateur pour le développement. Il fonctionne comme un vrai :
 * redirection vers une page de paiement, puis webhook signé en HMAC-SHA256.
 */
class SimulatedGateway implements PaymentGatewayInterface
{
    public function initiate(Transaction $transaction): array
    {
        return [
            'redirect_url' => route('payments.simulation.show', $transaction),
            'provider_ref' => 'SIM-'.Str::upper(Str::random(10)),
        ];
    }

    public function sign(string $body): string
    {
        return hash_hmac('sha256', $body, (string) config('payments.simulation.secret'));
    }

    public function verifySignature(Request $request): bool
    {
        // Sans secret configuré, on refuse tout : jamais de signature « vide » acceptée
        if (empty(config('payments.simulation.secret'))) {
            return false;
        }

        return hash_equals($this->sign($request->getContent()), (string) $request->header('X-Signature'));
    }

    public function parseWebhook(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        $data = is_array($data) ? $data : [];

        return [
            'event_id' => (string) ($data['event_id'] ?? ''),
            'reference' => (string) ($data['reference'] ?? ''),
            'status' => (string) ($data['status'] ?? ''),
            'amount' => (int) ($data['amount'] ?? 0),
        ];
    }
}