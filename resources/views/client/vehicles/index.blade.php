@extends('layouts.client')

@section('title', 'Mes véhicules')

@php
    use App\Enums\InsuranceStatus;

    $insuredCount = $vehicles->filter(
        fn ($v) => $v->insurance_status === InsuranceStatus::ACTIVE
    )->count();
@endphp

@section('content')
<div class="mx-auto max-w-7xl px-3 py-6 sm:px-6 lg:px-8">

    {{-- EN-TÊTE --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-emerald-700">MON ESPACE AUTOMOBILE</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Mes véhicules</h1>
            <p class="mt-2 text-sm text-gray-500">Gérez vos véhicules et suivez leurs informations importantes.</p>
        </div>

        <a href="{{ route('client.vehicles.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 hover:shadow-md">
            <span class="text-lg">+</span>
            Ajouter un véhicule
        </a>
    </div>

    {{-- STATISTIQUES --}}
    <div class="mb-7 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total des véhicules</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $vehicles->count() }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-2xl">🚗</div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Assurances valides</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $insuredCount }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-2xl">🛡️</div>
            </div>
        </div>
    </div>

    {{-- LISTE EN GRILLE --}}
    @if ($vehicles->isNotEmpty())
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
            @foreach ($vehicles as $vehicle)
                @php
                    $dueSoon = $vehicle->technicalVisitDueSoon();
                @endphp

                <a href="{{ route('client.vehicles.show', $vehicle) }}"
                   class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">

                    {{-- Photo --}}
                    <div class="relative h-44 w-full overflow-hidden bg-gray-100">
                        @if ($vehicle->photoUrl())
                            <img src="{{ $vehicle->photoUrl() }}"
                                 alt="Photo {{ $vehicle->plate }}"
                                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                <span class="text-5xl">🚗</span>
                            </div>
                        @endif

                        <span class="absolute left-3 top-3 rounded-lg bg-white/95 px-2.5 py-1 text-xs font-bold text-gray-800 shadow">
                            {{ $vehicle->year }}
                        </span>
                    </div>

                    {{-- Informations --}}
                    <div class="flex flex-1 flex-col p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Immatriculation</p>
                                <h2 class="truncate text-lg font-bold text-gray-900">{{ $vehicle->plate }}</h2>
                                <p class="truncate text-sm text-gray-600">{{ $vehicle->brand }} {{ $vehicle->model }}</p>
                            </div>
                        </div>

                        <span class="mt-3 inline-flex w-fit items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $vehicle->insurance_status->color() }}">
                            {{ $vehicle->insurance_status->label() }}
                        </span>

                        <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 text-xs text-gray-500">
                            <span>{{ $vehicle->color ?? 'Couleur non renseignée' }}</span>
                        </div>

                        <div class="mt-auto pt-3">
                            @if ($vehicle->technical_visit_expires_at)
                                <p class="text-xs text-gray-400">Prochaine visite technique</p>
                                <p class="mt-0.5 text-sm font-semibold {{ $dueSoon ? 'text-orange-600' : 'text-gray-700' }}">
                                    {{ $vehicle->technical_visit_expires_at->format('d/m/Y') }}
                                    @if ($dueSoon)
                                        <span class="ml-1 text-xs font-medium">⚠️ proche</span>
                                    @endif
                                </p>
                            @else
                                <p class="text-xs text-gray-400">Visite technique non renseignée</p>
                            @endif

                            <p class="mt-3 text-right text-sm font-semibold text-emerald-700 group-hover:text-emerald-900">
                                Voir les détails →
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        {{-- AUCUN VÉHICULE --}}
        <div class="rounded-2xl border border-gray-100 bg-white px-5 py-12 text-center shadow-sm sm:py-16">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-4xl">🚗</div>
            <h2 class="mt-5 text-xl font-bold text-gray-900">Aucun véhicule enregistré</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">
                Vous n'avez pas encore ajouté de véhicule. Commencez maintenant pour gérer vos informations,
                vos assurances et vos visites techniques.
            </p>
            <a href="{{ route('client.vehicles.create') }}"
               class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                + Ajouter mon premier véhicule
            </a>
        </div>
    @endif
</div>
@endsection