@extends('layouts.partner')
@section('title', 'Prospects assurance')

@section('content')
@include('partner._header', ['partner' => $partner])

@if ($partner->is_validated)
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Souscriptions actives</p>
            <p class="text-2xl font-bold text-emerald-700">{{ $activeCount }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Commissions dues à AutoSur.sn (indicatif)</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($commissionTotal, 0, ',', ' ') }} FCFA</p>
        </div>
    </div>

    <h2 class="mt-8 font-semibold">Demandes de souscription</h2>

    @forelse ($subscriptions as $sub)
        @php
            $quote = $sub->quote;
            $vehicle = $quote->vehicle;
            $requested = $sub->status === \App\Enums\SubscriptionStatus::REQUESTED;
        @endphp
        <div class="mt-3 rounded-lg bg-white p-4 shadow">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <p class="font-semibold">{{ $sub->user->name }}
                        @if ($sub->user->phone)
                            · <a href="tel:{{ $sub->user->phone }}" class="font-bold text-blue-800 underline">{{ $sub->user->phone }}</a>
                        @endif
                    </p>
                    <p class="text-sm text-gray-700">
                        {{ $vehicle->plate }} · {{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->year }} · {{ $quote->fiscal_power }} CV
                    </p>
                    <p class="text-sm text-gray-600">{{ $quote->cover_type->label() }} · {{ $quote->duration_months }} mois</p>
                    <p class="mt-1 text-sm text-gray-700">
                        Prime indicative : <strong>{{ number_format($sub->premium_amount, 0, ',', ' ') }} FCFA</strong>
                        · Commission AutoSur.sn : {{ $sub->commission_rate }} % ({{ number_format($sub->commission_amount, 0, ',', ' ') }} FCFA)
                    </p>
                    <p class="text-xs text-gray-500">Demandé le {{ $sub->created_at->format('d/m/Y') }}. Le client a accepté le partage de ses coordonnées.</p>
                    @if ($sub->starts_at)
                        <p class="text-sm text-gray-600">Du {{ $sub->starts_at->format('d/m/Y') }} au {{ $sub->ends_at->format('d/m/Y') }}</p>
                    @endif
                </div>
                <span class="rounded px-2 py-1 text-xs font-medium {{ $sub->status->color() }}">{{ $sub->status->label() }}</span>
            </div>

            @if ($requested)
                <div class="mt-3 flex flex-wrap items-end gap-3">
                    <form method="POST" action="{{ route('partner.insurance.activate', $sub) }}" class="flex flex-wrap items-end gap-2">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500" for="start-{{ $sub->id }}">Début du contrat</label>
                            <input id="start-{{ $sub->id }}" name="start_date" type="date" min="{{ now()->toDateString() }}"
                                   value="{{ now()->toDateString() }}" class="rounded border border-gray-300 p-1.5 text-sm">
                        </div>
                        <button class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">Activer</button>
                    </form>

                    <form method="POST" action="{{ route('partner.insurance.decline', $sub) }}" onsubmit="return confirm('Refuser cette demande ?')">
                        @csrf
                        <button class="rounded border border-red-300 px-4 py-2 text-sm text-red-700">Refuser</button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="mt-3 rounded-lg bg-white p-6 text-center text-gray-600 shadow">
            Aucune demande pour l'instant. Elles apparaissent quand un client choisit l'une de vos offres.
        </div>
    @endforelse
@endif
@endsection