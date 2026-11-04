@extends('layouts.client')
@section('title', 'Offres d\'assurance')

@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('client.insurance.index') }}" class="text-sm text-emerald-700 underline">← Mes devis</a>

    <div class="mt-3 rounded-lg bg-white p-5 shadow">
        <h1 class="text-xl font-bold">{{ $quote->vehicle->plate }} · {{ $quote->vehicle->brand }} {{ $quote->vehicle->model }}</h1>
        <p class="mt-1 text-sm text-gray-600">
            {{ $quote->cover_type->label() }} · {{ $quote->fiscal_power }} CV · {{ $quote->duration_months }} mois
            @if ($quote->vehicle_value) · valeur {{ number_format($quote->vehicle_value, 0, ',', ' ') }} FCFA @endif
        </p>
    </div>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    @if ($quote->subscription)
        @php($sub = $quote->subscription)
        <div class="mt-4 rounded-lg bg-white p-5 shadow">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-semibold">{{ $sub->offer->company_name }}</h2>
                <span class="rounded px-2 py-1 text-xs font-medium {{ $sub->status->color() }}">{{ $sub->status->label() }}</span>
            </div>
            <p class="mt-2 text-sm text-gray-700">Prime indicative : <strong>{{ number_format($sub->premium_amount, 0, ',', ' ') }} FCFA</strong></p>
            @if ($sub->starts_at)
                <p class="text-sm text-gray-600">Du {{ $sub->starts_at->format('d/m/Y') }} au {{ $sub->ends_at->format('d/m/Y') }}</p>
            @else
                <p class="mt-1 text-sm text-gray-600">L'assureur va vous contacter pour finaliser le contrat.</p>
            @endif
        </div>
    @else
        <p class="mt-4 text-xs text-gray-500">Les primes ci-dessous sont <strong>indicatives</strong>. Le tarif définitif est confirmé par l'assureur.</p>

        @forelse ($offers as $row)
            @php($offer = $row['offer'])
            <div class="mt-3 rounded-lg bg-white p-5 shadow">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold">{{ $offer->company_name }}</p>
                        <p class="text-sm text-gray-600">{{ $offer->name }}</p>
                        @if ($offer->description) <p class="mt-1 text-xs text-gray-500">{{ $offer->description }}</p> @endif
                    </div>
                    <p class="text-right text-xl font-bold text-emerald-700">
                        {{ number_format($row['premium'], 0, ',', ' ') }} <span class="text-sm font-normal">FCFA</span>
                    </p>
                </div>

                <form method="POST" action="{{ route('client.insurance.subscribe', [$quote, $offer]) }}" class="mt-3 border-t pt-3">
                    @csrf
                    <label class="flex items-start gap-2 text-xs text-gray-600">
                        <input type="checkbox" name="consent" value="1" class="mt-0.5">
                        J'accepte que mon nom, mon téléphone et les informations de ce véhicule soient transmis à {{ $offer->company_name }} pour établir mon contrat.
                    </label>
                    <button class="mt-3 rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">Choisir cette offre</button>
                </form>
            </div>
        @empty
            <div class="mt-4 rounded-lg bg-white p-6 text-center shadow">
                <p class="text-gray-600">Aucune offre disponible pour cette couverture pour le moment.</p>
            </div>
        @endforelse
    @endif
</div>
@endsection