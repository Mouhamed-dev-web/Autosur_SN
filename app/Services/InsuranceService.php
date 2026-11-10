<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\InsuranceOffer;
use App\Models\InsuranceQuote;
use App\Models\InsuranceSubscription;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Enums\PartnerType;
use App\Models\Partner;
use App\Notifications\StatusNotification;

class InsuranceService
{
    public const COMMISSION_MIN = 5.0;

    public const COMMISSION_MAX = 12.0;

    /** Le taux de commission reste toujours entre 5 % et 12 %. */
    public function commissionRate(InsuranceOffer $offer): float
    {
        return max(self::COMMISSION_MIN, min(self::COMMISSION_MAX, $offer->commission_rate));
    }

    public function commission(int $premium, float $rate): int
    {
        return (int) round($premium * $rate / 100);
    }

    /** Prime indicative pour la durée demandée, arrondie à 100 FCFA. */
    public function premium(InsuranceOffer $offer, InsuranceQuote $quote): int
    {
        $annual = $offer->base_premium
            + $offer->per_cv_premium * $quote->fiscal_power
            + ($quote->vehicle_value ?? 0) * $offer->value_rate / 100;

        return (int) (round($annual * $quote->duration_months / 12 / 100) * 100);
    }

    /** @return Collection<int, array{offer: InsuranceOffer, premium: int}> */
    public function offersFor(InsuranceQuote $quote): Collection
    {
        return InsuranceOffer::where('is_active', true)
            ->where('cover_type', $quote->cover_type->value)
            ->get()
            ->map(fn (InsuranceOffer $offer) => [
                'offer' => $offer,
                'premium' => $this->premium($offer, $quote),
            ])
            ->sortBy('premium')
            ->values();
    }

    public function subscribe(InsuranceQuote $quote, InsuranceOffer $offer): InsuranceSubscription
    {
        if (! $offer->is_active || $offer->cover_type !== $quote->cover_type) {
            throw new DomainException('Cette offre ne correspond pas à votre demande.');
        }

        if ($quote->subscription()->exists()) {
            throw new DomainException('Une souscription existe déjà pour ce devis.');
        }

        $premium = $this->premium($offer, $quote);
        $rate = $this->commissionRate($offer);

        return InsuranceSubscription::create([
            'user_id' => $quote->user_id,
            'insurance_quote_id' => $quote->id,
            'insurance_offer_id' => $offer->id,
            'status' => SubscriptionStatus::REQUESTED,
            'premium_amount' => $premium,
            'commission_rate' => $rate,
            'commission_amount' => $this->commission($premium, $rate),
            'consented_at' => now(),
        ]);
    }

    /** Appelée quand l'assureur confirme (espace partenaire, étape 10). */
    public function activate(InsuranceSubscription $subscription, ?CarbonInterface $start = null): InsuranceSubscription
    {
        if ($subscription->status !== SubscriptionStatus::REQUESTED) {
            throw new DomainException('Seule une demande en cours peut être activée.');
        }

        $start = ($start ?? now())->copy()->startOfDay();
        $quote = $subscription->quote()->with('vehicle')->first();
        $end = $start->copy()->addMonths($quote->duration_months);

        DB::transaction(function () use ($subscription, $quote, $start, $end) {
            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'starts_at' => $start->toDateString(),
                'ends_at' => $end->toDateString(),
                'activated_at' => now(),
            ]);

            $quote->vehicle->update([
                'insurance_status' => 'active',
                'insurance_expires_at' => $end->toDateString(),
            ]);
        });

        return $subscription;
    }

    public function cancel(InsuranceSubscription $subscription): InsuranceSubscription
    {
        if ($subscription->status !== SubscriptionStatus::REQUESTED) {
            throw new DomainException('Seule une demande en cours peut être annulée.');
        }

        $subscription->update(['status' => SubscriptionStatus::CANCELLED]);

        return $subscription;
    }
    public function activateBy(InsuranceSubscription $subscription, Partner $partner, ?CarbonInterface $start = null): InsuranceSubscription
    {
        $this->assertInsurer($subscription, $partner);

        $this->activate($subscription, $start);
        $this->notifyClient(
            $subscription,
            "Votre assurance {$subscription->offer->company_name} est activée jusqu'au {$subscription->ends_at->format('d/m/Y')}.",
        );

        return $subscription;
    }

    public function declineBy(InsuranceSubscription $subscription, Partner $partner): InsuranceSubscription
    {
        $this->assertInsurer($subscription, $partner);

        $this->cancel($subscription);
        $this->notifyClient(
            $subscription,
            "{$subscription->offer->company_name} n'a pas pu donner suite à votre demande. Vous pouvez comparer d'autres offres.",
        );

        return $subscription;
    }

    private function assertInsurer(InsuranceSubscription $subscription, Partner $partner): void
    {
        if ($partner->type !== PartnerType::ASSURANCE || ! $partner->is_validated) {
            throw new DomainException('Votre compte assureur doit être validé par AutoSur.sn.');
        }

        if ($subscription->offer->partner_id !== $partner->id) {
            throw new DomainException('Cette demande ne concerne pas votre compagnie.');
        }
    }

    private function notifyClient(InsuranceSubscription $subscription, string $message): void
    {
        $subscription->user->notify(new StatusNotification(
            'Assurance',
            $message,
            route('client.insurance.show', $subscription->insurance_quote_id),
        ));
    }
}