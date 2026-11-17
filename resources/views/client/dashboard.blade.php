@extends('layouts.client')
@section('title', 'Tableau de bord')

@php
    $user = auth()->user();
    $first = $user->first_name ?: \Illuminate\Support\Str::before($user->name, ' ');

    $cards = [
        ['label' => 'Véhicules', 'value' => $stats['vehicles'], 'icon' => '🚗', 'tone' => 'bg-emerald-100', 'url' => route('client.vehicles.index')],
        ['label' => 'Assurances valides', 'value' => $stats['insured'].' / '.$stats['vehicles'], 'icon' => '🛡️', 'tone' => 'bg-blue-100', 'url' => route('client.insurance.index')],
        ['label' => 'Échéances (30 j)', 'value' => $stats['deadlines'], 'icon' => '⏰', 'tone' => 'bg-orange-100', 'url' => route('client.alerts.index')],
        ['label' => 'Documents', 'value' => $stats['documents'], 'icon' => '🗂️', 'tone' => 'bg-indigo-100', 'url' => route('client.documents.index')],
        ['label' => 'Alertes non lues', 'value' => $stats['unread'], 'icon' => '🔔', 'tone' => 'bg-red-100', 'url' => route('client.alerts.index')],
        ['label' => 'Payé', 'value' => number_format($stats['paid'], 0, ',', ' ').' FCFA', 'icon' => '💳', 'tone' => 'bg-gray-100', 'url' => route('client.payments.index')],
    ];

    $quick = [
        ['label' => 'Ajouter un véhicule', 'icon' => '➕', 'url' => route('client.vehicles.create')],
        ['label' => 'Ajouter un document', 'icon' => '📄', 'url' => route('client.documents.create')],
        ['label' => 'Demander un devis', 'icon' => '🛡️', 'url' => route('client.insurance.create')],
        ['label' => 'Rendez-vous visite', 'icon' => '🔧', 'url' => route('client.visits.create')],
    ];

    $levelClass = [
        'expired' => 'bg-red-100 text-red-800',
        'urgent' => 'bg-orange-100 text-orange-800',
        'soon' => 'bg-emerald-100 text-emerald-800',
    ];
@endphp

@section('content')
<div class="mx-auto max-w-6xl">

    {{-- En-tête --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-emerald-700">MON ESPACE AUTOMOBILE</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">Bonjour {{ $first }} 👋</h1>
            <p class="mt-1 text-sm text-gray-500">Voici l'essentiel de vos véhicules et de vos échéances.</p>
        </div>
        <a href="{{ route('client.sos.create') }}"
           class="rounded-xl bg-red-700 px-5 py-3 text-center font-bold text-white shadow hover:bg-red-800">
            🚨 En panne ? Demander un dépanneur
        </a>
    </div>

    {{-- Statistiques --}}
    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-3">
        @foreach ($cards as $card)
            <a href="{{ $card['url'] }}" class="flex items-center justify-between rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div>
                    <p class="text-xs text-gray-500 sm:text-sm">{{ $card['label'] }}</p>
                    <p class="mt-1 text-xl font-bold text-gray-900 sm:text-2xl">{{ $card['value'] }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl text-xl sm:h-12 sm:w-12 sm:text-2xl {{ $card['tone'] }}">{{ $card['icon'] }}</div>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">

        {{-- Colonne principale --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Échéances --}}
            <section class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-gray-900">Échéances à venir</h2>

                @forelse ($deadlines as $d)
                    <a href="{{ $d['url'] }}" class="mt-3 flex items-center justify-between gap-3 rounded-lg border border-gray-100 p-3 hover:bg-gray-50">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-900">{{ $d['label'] }} · {{ $d['subject'] }}</p>
                            <p class="text-xs text-gray-500">{{ $d['date']->format('d/m/Y') }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $levelClass[$d['level']] }}">
                            @if ($d['days'] < 0)
                                Expiré depuis {{ abs($d['days']) }} j
                            @elseif ($d['days'] === 0)
                                Aujourd'hui
                            @else
                                Dans {{ $d['days'] }} j
                            @endif
                        </span>
                    </a>
                @empty
                    <p class="mt-3 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-800">
                        ✓ Aucune échéance dans les 30 prochains jours.
                    </p>
                @endforelse
            </section>

            {{-- Véhicules --}}
            <section class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Mes véhicules</h2>
                    <a href="{{ route('client.vehicles.index') }}" class="text-sm text-emerald-700 underline">Tout voir</a>
                </div>

                @forelse ($vehicles->take(4) as $vehicle)
                    <a href="{{ route('client.vehicles.show', $vehicle) }}" class="mt-3 flex items-center gap-3 rounded-lg border border-gray-100 p-3 hover:bg-gray-50">
                        @if ($vehicle->photoUrl())
                            <img src="{{ $vehicle->photoUrl() }}" alt="" class="h-12 w-16 rounded object-cover">
                        @else
                            <div class="flex h-12 w-16 items-center justify-center rounded bg-gray-100 text-2xl">🚗</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-gray-900">{{ $vehicle->plate }}</p>
                            <p class="truncate text-sm text-gray-600">{{ $vehicle->brand }} {{ $vehicle->model }} · {{ $vehicle->year }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $vehicle->insurance_status->color() }}">
                            {{ $vehicle->insurance_status->label() }}
                        </span>
                    </a>
                @empty
                    <div class="mt-3 rounded-lg bg-gray-50 p-6 text-center">
                        <p class="text-sm text-gray-600">Vous n'avez pas encore de véhicule.</p>
                        <a href="{{ route('client.vehicles.create') }}" class="mt-2 inline-block text-sm font-semibold text-emerald-700 underline">Ajouter mon premier véhicule</a>
                    </div>
                @endforelse
            </section>

            {{-- Rendez-vous --}}
            <section class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Prochains rendez-vous</h2>
                    <a href="{{ route('client.visits.index') }}" class="text-sm text-emerald-700 underline">Voir tout</a>
                </div>

                @forelse ($visits as $visit)
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-100 p-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $visit->vehicle->plate }} · {{ $visit->scheduled_at->translatedFormat('l d/m à H\hi') }}</p>
                            <p class="text-xs text-gray-500">{{ $visit->partner?->company_name ?? 'Garage en attente d\'attribution' }}</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $visit->status->color() }}">{{ $visit->status->label() }}</span>
                    </div>
                @empty
                    <p class="mt-3 text-sm text-gray-500">Aucun rendez-vous prévu.
                        <a href="{{ route('client.visits.create') }}" class="text-emerald-700 underline">Prendre rendez-vous</a></p>
                @endforelse
            </section>
        </div>

        {{-- Colonne latérale --}}
        <div class="space-y-6">

            {{-- Actions rapides --}}
            <section class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-gray-900">Actions rapides</h2>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    @foreach ($quick as $action)
                        <a href="{{ $action['url'] }}" class="flex flex-col items-center rounded-xl border border-gray-100 p-3 text-center text-xs font-medium text-gray-700 hover:bg-emerald-50">
                            <span class="text-2xl">{{ $action['icon'] }}</span>
                            <span class="mt-1">{{ $action['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- Alertes --}}
            <section class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Dernières alertes</h2>
                    <a href="{{ route('client.alerts.index') }}" class="text-sm text-emerald-700 underline">Voir tout</a>
                </div>

                @forelse ($alerts as $alert)
                    <a href="{{ $alert->data['url'] ?? route('client.alerts.index') }}"
                       class="mt-3 block rounded-lg border-l-4 p-3 text-sm {{ $alert->read_at ? 'border-gray-200 bg-gray-50' : 'border-orange-500 bg-orange-50' }}">
                        <p class="font-medium text-gray-900">{{ $alert->data['title'] ?? 'Alerte' }}</p>
                        <p class="text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($alert->data['message'] ?? '', 90) }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ $alert->created_at->diffForHumans() }}</p>
                    </a>
                @empty
                    <p class="mt-3 text-sm text-gray-500">Aucune alerte pour l'instant.</p>
                @endforelse
            </section>

            {{-- Activité --}}
            <section class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-gray-900">Activité récente</h2>

                @forelse ($activity as $row)
                    <a href="{{ $row['url'] }}" class="mt-3 flex items-start gap-2 text-sm hover:underline">
                        <span>{{ $row['icon'] }}</span>
                        <span class="flex-1 text-gray-700">{{ $row['text'] }}
                            <span class="block text-xs text-gray-400">{{ $row['date']->format('d/m/Y H\hi') }}</span>
                        </span>
                    </a>
                @empty
                    <p class="mt-3 text-sm text-gray-500">Rien pour l'instant.</p>
                @endforelse
            </section>
        </div>
    </div>
</div>
@endsection