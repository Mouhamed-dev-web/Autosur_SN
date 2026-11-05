<?php

namespace App\Services;

use App\Enums\PartnerType;
use App\Enums\SosStatus;
use App\Models\Partner;
use App\Models\SosRequest;
use App\Models\User;
use App\Notifications\SosStatusNotification;
use App\Support\Geo;
use DomainException;
use Illuminate\Support\Collection;

class SosService
{
    /** Une demande sans réponse n'est plus diffusée après ce délai. */
    public const PENDING_TTL_MINUTES = 60;

    public function create(User $user, array $data): SosRequest
    {
        if ($user->sosRequests()->whereIn('status', SosStatus::openValues())->exists()) {
            throw new DomainException('Vous avez déjà une demande SOS en cours.');
        }

        return $user->sosRequests()->create($data + ['status' => SosStatus::PENDING]);
    }

    /**
     * Demandes visibles par un dépanneur : en attente, non refusées par lui,
     * dans son rayon (si les positions sont connues), les plus proches d'abord.
     *
     * @return Collection<int, array{request: SosRequest, distance: float|null}>
     */
    public function availableFor(Partner $partner): Collection
    {
        if (! $this->canWork($partner) || $this->currentJob($partner)) {
            return collect();
        }

        return SosRequest::with('vehicle')
            ->where('status', SosStatus::PENDING->value)
            ->where('created_at', '>=', now()->subMinutes(self::PENDING_TTL_MINUTES))
            ->whereDoesntHave('declinedBy', fn ($q) => $q->where('partners.id', $partner->id))
            ->oldest()
            ->get()
            ->map(fn (SosRequest $sos) => [
                'request' => $sos,
                'distance' => $this->distance($partner, $sos),
            ])
            ->filter(fn (array $row) => $row['distance'] === null || $row['distance'] <= $partner->radius_km)
            ->sortBy(fn (array $row) => $row['distance'] ?? PHP_INT_MAX)
            ->values();
    }

    public function currentJob(Partner $partner): ?SosRequest
    {
        return $partner->sosRequests()
            ->whereIn('status', [SosStatus::ACCEPTED->value, SosStatus::ARRIVED->value])
            ->with(['vehicle', 'user'])
            ->first();
    }

    /** Le premier dépanneur qui accepte obtient la demande (mise à jour atomique). */
    public function accept(SosRequest $sos, Partner $partner): SosRequest
    {
        if (! $this->canWork($partner)) {
            throw new DomainException('Votre compte doit être validé et marqué disponible.');
        }

        if ($this->currentJob($partner)) {
            throw new DomainException('Terminez votre intervention en cours avant d\'en accepter une autre.');
        }

        $updated = SosRequest::whereKey($sos->id)
            ->where('status', SosStatus::PENDING->value)
            ->update([
                'status' => SosStatus::ACCEPTED->value,
                'partner_id' => $partner->id,
                'accepted_at' => now(),
            ]);

        if ($updated === 0) {
            throw new DomainException('Cette demande a déjà été prise ou annulée.');
        }

        $sos->refresh();
        $this->notifyClient($sos, "{$partner->company_name} est en route. Contact : {$partner->contact_phone}.");

        return $sos;
    }

    public function decline(SosRequest $sos, Partner $partner): void
    {
        $sos->declinedBy()->syncWithoutDetaching([$partner->id]);
    }

    public function arrive(SosRequest $sos, Partner $partner): SosRequest
    {
        $this->assertAssigned($sos, $partner, SosStatus::ACCEPTED);

        $sos->update(['status' => SosStatus::ARRIVED, 'arrived_at' => now()]);
        $this->notifyClient($sos, 'Votre dépanneur est arrivé sur place.');

        return $sos;
    }

    public function complete(SosRequest $sos, Partner $partner): SosRequest
    {
        $this->assertAssigned($sos, $partner, SosStatus::ARRIVED);

        $sos->update(['status' => SosStatus::COMPLETED, 'completed_at' => now()]);
        $this->notifyClient($sos, 'Intervention terminée. Merci d\'avoir utilisé AutoSur.sn.');

        return $sos;
    }

    public function cancel(SosRequest $sos): SosRequest
    {
        if (! $sos->canBeCancelled()) {
            throw new DomainException('Cette demande ne peut plus être annulée.');
        }

        $sos->update(['status' => SosStatus::CANCELLED, 'cancelled_at' => now()]);

        return $sos;
    }

    private function canWork(Partner $partner): bool
    {
        return $partner->type === PartnerType::DEPANNEUR
            && $partner->is_validated
            && $partner->is_available;
    }

    private function distance(Partner $partner, SosRequest $sos): ?float
    {
        if (! $partner->hasBaseLocation() || ! $sos->hasCoordinates()) {
            return null;
        }

        return round(Geo::distanceKm($partner->base_lat, $partner->base_lng, $sos->lat, $sos->lng), 1);
    }

    private function assertAssigned(SosRequest $sos, Partner $partner, SosStatus $expected): void
    {
        if ($sos->partner_id !== $partner->id) {
            throw new DomainException('Cette intervention ne vous est pas attribuée.');
        }

        if ($sos->status !== $expected) {
            throw new DomainException("Statut attendu : {$expected->label()}.");
        }
    }

    private function notifyClient(SosRequest $sos, string $message): void
    {
        $sos->user->notify(new SosStatusNotification($sos->id, $message));
    }
}