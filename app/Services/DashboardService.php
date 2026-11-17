<?php

namespace App\Services;

use App\Enums\InsuranceStatus;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DashboardService
{
    /** Fenêtre d'affichage des échéances, en jours. */
    public const HORIZON_DAYS = 30;

    /** @return array<string, mixed> */
    public function for(User $user): array
    {
        $vehicles = $user->vehicles()->latest()->get();
        $deadlines = $this->deadlines($user, $vehicles);

        return [
            'vehicles' => $vehicles,
            'deadlines' => $deadlines->take(6),
            'stats' => [
                'vehicles' => $vehicles->count(),
                'insured' => $vehicles->filter(fn ($v) => $v->insurance_status === InsuranceStatus::ACTIVE)->count(),
                'deadlines' => $deadlines->count(),
                'documents' => $user->documents()->count(),
                'unread' => $user->unreadNotifications()->count(),
                'paid' => (int) $user->transactions()->where('status', 'paid')->sum('amount'),
            ],
            'visits' => $user->technicalVisits()
                ->with(['vehicle', 'partner'])
                ->whereIn('status', ['requested', 'confirmed'])
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->limit(3)
                ->get(),
            'alerts' => $user->notifications()->limit(4)->get(),
            'activity' => $this->activity($user),
        ];
    }

    /**
     * Échéances expirées ou dans les 30 prochains jours, triées par date.
     *
     * @param  Collection<int, \App\Models\Vehicle>  $vehicles
     * @return Collection<int, array{label: string, subject: string, date: CarbonInterface, days: int, level: string, url: string}>
     */
    private function deadlines(User $user, Collection $vehicles): Collection
    {
        $items = collect();

        foreach ($vehicles as $vehicle) {
            $url = route('client.vehicles.show', $vehicle);

            $items->push($this->item('Assurance', $vehicle->plate, $vehicle->insurance_expires_at, $url));
            $items->push($this->item('Visite technique', $vehicle->plate, $vehicle->technical_visit_expires_at, $url));
        }

        foreach ($user->documents()->whereNotNull('expires_at')->get() as $document) {
            $items->push($this->item($document->type->label(), $document->title, $document->expires_at, route('client.documents.index')));
        }

        return $items
            ->filter()
            ->filter(fn (array $item) => $item['days'] <= self::HORIZON_DAYS)
            ->sortBy('days')
            ->values();
    }

    /** @return array{label: string, subject: string, date: CarbonInterface, days: int, level: string, url: string}|null */
    private function item(string $label, string $subject, ?CarbonInterface $date, string $url): ?array
    {
        if ($date === null) {
            return null;
        }

        $days = (int) now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);

        return [
            'label' => $label,
            'subject' => $subject,
            'date' => $date,
            'days' => $days,
            'level' => match (true) {
                $days < 0 => 'expired',
                $days <= 7 => 'urgent',
                default => 'soon',
            },
            'url' => $url,
        ];
    }

    /** SOS et paiements récents, mélangés par date. */
    private function activity(User $user): Collection
    {
        $sos = $user->sosRequests()->latest()->limit(3)->get()->map(fn ($s) => [
            'icon' => '🚨',
            'text' => 'SOS : '.$s->problem->label().' ('.$s->status->label().')',
            'date' => $s->created_at,
            'url' => route('client.sos.show', $s),
        ]);

        $payments = $user->transactions()->latest()->limit(3)->get()->map(fn ($t) => [
            'icon' => '💳',
            'text' => 'Paiement '.$t->amountForHumans().' ('.$t->status->label().')',
            'date' => $t->created_at,
            'url' => route('client.payments.result', $t),
        ]);

        return $sos->concat($payments)->sortByDesc('date')->take(5)->values();
    }
}