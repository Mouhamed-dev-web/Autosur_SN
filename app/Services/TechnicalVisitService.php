<?php

namespace App\Services;

use App\Enums\PartnerType;
use App\Enums\VisitStatus;
use App\Models\Partner;
use App\Models\TechnicalVisit;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\StatusNotification;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class TechnicalVisitService
{
    /** Frais de service, toujours dans la fourchette autorisée. */
    public function fee(): int
    {
        $config = config('autosur.technical_visit');
        $fee = Setting::int('technical_visit_fee', (int) $config['fee']);

        return max($config['fee_min'], min($config['fee_max'], $fee));
    }

    public function create(
        User $user,
        Vehicle $vehicle,
        CarbonInterface $at,
        ?string $notes = null,
        ?Partner $partner = null,
    ): TechnicalVisit {
        return $user->technicalVisits()->create([
            'vehicle_id' => $vehicle->id,
            'partner_id' => $partner?->id,
            'scheduled_at' => $at,
            'status' => VisitStatus::REQUESTED,
            'fee_amount' => $this->fee(),
            'notes' => $notes,
        ]);
    }

    public function confirm(TechnicalVisit $visit): TechnicalVisit
    {
        $this->assertStatus($visit, VisitStatus::REQUESTED);
        $visit->update(['status' => VisitStatus::CONFIRMED]);

        return $visit;
    }

    public function cancel(TechnicalVisit $visit): TechnicalVisit
    {
        if (! $visit->status->isActive()) {
            throw new DomainException('Cette visite ne peut plus être annulée.');
        }
        $visit->update(['status' => VisitStatus::CANCELLED]);

        return $visit;
    }

    /** Marque la visite comme effectuée et met à jour les dates du véhicule. */
    public function complete(TechnicalVisit $visit): TechnicalVisit
    {
        $this->assertStatus($visit, VisitStatus::CONFIRMED);

        DB::transaction(function () use ($visit) {
            $visit->update([
                'status' => VisitStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            $visit->vehicle->update([
                'technical_visit_last_at' => now()->toDateString(),
                'technical_visit_expires_at' => now()
                    ->addMonths(Setting::int('technical_visit_validity_months', (int) config('autosur.technical_visit.validity_months')))
                    ->toDateString(),
            ]);
        });

        return $visit;
    }

    /**
     * Le garage confirme. Si la demande n'est pas encore attribuée, le premier
     * garage qui confirme l'obtient (mise à jour atomique).
     */
    public function confirmBy(TechnicalVisit $visit, Partner $partner): TechnicalVisit
    {
        $this->assertGarage($partner);

        $updated = TechnicalVisit::whereKey($visit->id)
            ->where('status', VisitStatus::REQUESTED->value)
            ->where(fn ($q) => $q->whereNull('partner_id')->orWhere('partner_id', $partner->id))
            ->update([
                'status' => VisitStatus::CONFIRMED->value,
                'partner_id' => $partner->id,
            ]);

        if ($updated === 0) {
            throw new DomainException('Cette demande a déjà été prise ou annulée.');
        }

        $visit->refresh();
        $this->notify(
            $visit,
            "Votre rendez-vous du {$visit->scheduled_at->format('d/m/Y à H\hi')} est confirmé chez {$partner->company_name} ({$partner->contact_phone}).",
        );

        return $visit;
    }

    public function rejectBy(TechnicalVisit $visit, Partner $partner): TechnicalVisit
    {
        $this->assertGarage($partner);
        $this->assertOwned($visit, $partner);

        $this->cancel($visit);
        $this->notify(
            $visit,
            "{$partner->company_name} ne peut pas honorer votre rendez-vous du {$visit->scheduled_at->format('d/m/Y')}. Vous pouvez en demander un autre.",
        );

        return $visit;
    }

    public function completeBy(TechnicalVisit $visit, Partner $partner): TechnicalVisit
    {
        $this->assertGarage($partner);
        $this->assertOwned($visit, $partner);

        $this->complete($visit);
        $this->notify(
            $visit,
            "Visite technique effectuée chez {$partner->company_name}. Prochaine échéance : {$visit->vehicle->technical_visit_expires_at->format('d/m/Y')}. Pensez à noter le garage.",
        );

        return $visit;
    }

    private function assertGarage(Partner $partner): void
    {
        if ($partner->type !== PartnerType::GARAGE || ! $partner->is_validated) {
            throw new DomainException('Votre compte garage doit être validé par AutoSur.sn.');
        }
    }

    private function assertOwned(TechnicalVisit $visit, Partner $partner): void
    {
        if ($visit->partner_id !== $partner->id) {
            throw new DomainException('Ce rendez-vous ne vous est pas attribué.');
        }
    }

    private function assertStatus(TechnicalVisit $visit, VisitStatus $expected): void
    {
        if ($visit->status !== $expected) {
            throw new DomainException("Statut attendu : {$expected->label()}.");
        }
    }

    private function notify(TechnicalVisit $visit, string $message): void
    {
        $visit->user->notify(new StatusNotification('Visite technique', $message, route('client.visits.index')));
    }
    
}