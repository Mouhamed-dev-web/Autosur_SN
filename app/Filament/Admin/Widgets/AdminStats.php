<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\SosStatus;
use App\Models\ContactMessage;
use App\Models\InsuranceSubscription;
use App\Models\Partner;
use App\Models\SosRequest;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $paid = (int) Transaction::where('status', 'paid')->sum('amount');
        $commissions = (int) InsuranceSubscription::where('status', 'active')->sum('commission_amount');

        return [
            Stat::make('Automobilistes', User::role('client')->count()),
            Stat::make('Partenaires à valider', Partner::where('is_validated', false)->count())
                ->description(Partner::where('is_validated', true)->count().' validés'),
            Stat::make('SOS en cours', SosRequest::whereIn('status', SosStatus::openValues())->count()),
            Stat::make('Paiements encaissés', number_format($paid, 0, ',', ' ').' FCFA')
                ->description('Frais de visite technique'),
            Stat::make('Commissions assurance', number_format($commissions, 0, ',', ' ').' FCFA')
                ->description('Souscriptions actives'),
            Stat::make('Messages non traités', ContactMessage::whereNull('handled_at')->count()),
        ];
    }
}