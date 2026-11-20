<?php

namespace App\Services;

use App\Enums\PartnerType;
use App\Enums\SosStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\VisitStatus;
use App\Models\InsuranceSubscription;
use App\Models\Partner;
use App\Models\TechnicalVisit;

class PartnerDashboardService
{
    /** @return array<string, mixed> */
    public function for(Partner $partner): array
    {
        $base = [
            'partner' => $partner,
            'rating' => $partner->averageRating(),
            'ratingCount' => $partner->visibleRatings()->count(),
        ];

        return $base + match ($partner->type) {
            PartnerType::GARAGE => $this->garage($partner),
            PartnerType::DEPANNEUR => $this->towing($partner),
            PartnerType::ASSURANCE => $this->insurer($partner),
        };
    }

    /** @return array<string, mixed> */
    private function garage(Partner $partner): array
    {
        $mine = $partner->visits();

        return [
            'cards' => [
                ['label' => 'À traiter', 'value' => (clone $mine)->where('status', VisitStatus::REQUESTED->value)->count(), 'icon' => '⏳', 'tone' => 'bg-orange-100'],
                ['label' => 'Confirmés', 'value' => (clone $mine)->where('status', VisitStatus::CONFIRMED->value)->count(), 'icon' => '✅', 'tone' => 'bg-blue-100'],
                ['label' => 'Demandes disponibles', 'value' => $partner->is_validated
                    ? TechnicalVisit::where('status', VisitStatus::REQUESTED->value)->whereNull('partner_id')->where('scheduled_at', '>', now())->count()
                    : 0, 'icon' => '📥', 'tone' => 'bg-emerald-100'],
                ['label' => 'Visites ce mois', 'value' => (clone $mine)->where('status', VisitStatus::COMPLETED->value)
                    ->where('completed_at', '>=', now()->startOfMonth())->count(), 'icon' => '🔧', 'tone' => 'bg-indigo-100'],
            ],
            'listTitle' => 'Prochains rendez-vous',
            'listUrl' => route('partner.visits.index'),
            'rows' => $partner->visits()->with('vehicle')
                ->whereIn('status', [VisitStatus::REQUESTED->value, VisitStatus::CONFIRMED->value])
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')->limit(5)->get()
                ->map(fn ($v) => [
                    'title' => $v->vehicle->plate.' · '.$v->vehicle->brand.' '.$v->vehicle->model,
                    'sub' => $v->scheduled_at->translatedFormat('l d/m à H\hi'),
                    'badge' => $v->status->label(),
                    'badgeClass' => $v->status->color(),
                ]),
            'emptyText' => 'Aucun rendez-vous à venir.',
            'primary' => ['label' => 'Gérer mes rendez-vous', 'url' => route('partner.visits.index')],
        ];
    }

    /** @return array<string, mixed> */
    private function towing(Partner $partner): array
    {
        $service = app(SosService::class);
        $current = $service->currentJob($partner);

        return [
            'cards' => [
                ['label' => 'Statut', 'value' => $partner->is_validated ? ($partner->is_available ? 'Disponible' : 'Indisponible') : 'En validation', 'icon' => '📡', 'tone' => $partner->is_available ? 'bg-emerald-100' : 'bg-gray-100'],
                ['label' => 'Demandes proches', 'value' => $service->availableFor($partner)->count(), 'icon' => '🚨', 'tone' => 'bg-red-100'],
                ['label' => 'Interventions terminées', 'value' => $partner->sosRequests()->where('status', SosStatus::COMPLETED->value)->count(), 'icon' => '✔️', 'tone' => 'bg-indigo-100'],
                ['label' => 'Ce mois', 'value' => $partner->sosRequests()->where('status', SosStatus::COMPLETED->value)
                    ->where('completed_at', '>=', now()->startOfMonth())->count(), 'icon' => '📅', 'tone' => 'bg-blue-100'],
            ],
            'listTitle' => 'Intervention en cours',
            'listUrl' => route('partner.sos.index'),
            'rows' => collect($current ? [[
                'title' => $current->problem->label(),
                'sub' => $current->vehicle ? $current->vehicle->brand.' '.$current->vehicle->model : 'Véhicule',
                'badge' => $current->status->label(),
                'badgeClass' => $current->status->color(),
            ]] : []),
            'emptyText' => 'Aucune intervention en cours.',
            'primary' => ['label' => 'Voir les demandes SOS', 'url' => route('partner.sos.index')],
        ];
    }

    /** @return array<string, mixed> */
    private function insurer(Partner $partner): array
    {
        $subs = InsuranceSubscription::whereHas('offer', fn ($q) => $q->where('partner_id', $partner->id));

        return [
            'cards' => [
                ['label' => 'Prospects en attente', 'value' => (clone $subs)->where('status', SubscriptionStatus::REQUESTED->value)->count(), 'icon' => '⏳', 'tone' => 'bg-orange-100'],
                ['label' => 'Contrats actifs', 'value' => (clone $subs)->where('status', SubscriptionStatus::ACTIVE->value)->count(), 'icon' => '🛡️', 'tone' => 'bg-emerald-100'],
                ['label' => 'Commissions dues (FCFA)', 'value' => number_format((int) (clone $subs)->where('status', SubscriptionStatus::ACTIVE->value)->sum('commission_amount'), 0, ',', ' '), 'icon' => '💰', 'tone' => 'bg-indigo-100'],
                ['label' => 'Offres actives', 'value' => $partner->insuranceOffers()->where('is_active', true)->count(), 'icon' => '📋', 'tone' => 'bg-blue-100'],
            ],
            'listTitle' => 'Derniers prospects',
            'listUrl' => route('partner.insurance.index'),
            'rows' => (clone $subs)->with(['quote.vehicle', 'user'])->latest()->limit(5)->get()
                ->map(fn ($s) => [
                    'title' => \Illuminate\Support\Str::before($s->user->name, ' ').' · '.$s->quote->vehicle->plate,
                    'sub' => $s->quote->cover_type->label().' · '.number_format($s->premium_amount, 0, ',', ' ').' FCFA',
                    'badge' => $s->status->label(),
                    'badgeClass' => $s->status->color(),
                ]),
            'emptyText' => 'Aucun prospect pour l\'instant.',
            'primary' => ['label' => 'Gérer les prospects', 'url' => route('partner.insurance.index')],
        ];
    }
}