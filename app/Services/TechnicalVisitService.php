<?php

namespace App\Services;

use App\Enums\VisitStatus;
use App\Models\TechnicalVisit;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

class TechnicalVisitService
{
    /** Frais de service, toujours dans la fourchette autorisée. */
    public function fee(): int
    {
        $config = config('autosur.technical_visit');

        return max($config['fee_min'], min($config['fee_max'], $config['fee']));
    }

    public function create(User $user, Vehicle $vehicle, CarbonInterface $at, ?string $notes = null): TechnicalVisit
    {
        return $user->technicalVisits()->create([
            'vehicle_id' => $vehicle->id,
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
                    ->addMonths(config('autosur.technical_visit.validity_months'))
                    ->toDateString(),
            ]);
        });

        return $visit;
    }

    private function assertStatus(TechnicalVisit $visit, VisitStatus $expected): void
    {
        if ($visit->status !== $expected) {
            throw new DomainException("Statut attendu : {$expected->label()}.");
        }
    }
}