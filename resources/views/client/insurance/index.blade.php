@extends('layouts.client')
@section('title', 'Assurance')

@section('content')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">Assurance</h1>
    <a href="{{ route('client.insurance.create') }}" class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">+ Demander un devis</a>
</div>

@forelse ($quotes as $quote)
    <a href="{{ route('client.insurance.show', $quote) }}" class="mt-4 block rounded-lg bg-white p-4 shadow hover:shadow-md">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <p class="font-semibold">{{ $quote->vehicle->plate }}
                    <span class="font-normal text-gray-600">· {{ $quote->vehicle->brand }} {{ $quote->vehicle->model }}</span>
                </p>
                <p class="text-sm text-gray-600">{{ $quote->cover_type->label() }} · {{ $quote->duration_months }} mois</p>
                <p class="text-xs text-gray-500">Demandé le {{ $quote->created_at->format('d/m/Y') }}</p>
            </div>
            @if ($quote->subscription)
                <span class="rounded px-2 py-1 text-xs font-medium {{ $quote->subscription->status->color() }}">
                    {{ $quote->subscription->offer->company_name }} · {{ $quote->subscription->status->label() }}
                </span>
            @else
                <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600">Offres à comparer</span>
            @endif
        </div>
    </a>
@empty
    <div class="mt-6 rounded-lg bg-white p-8 text-center shadow">
        <p class="text-gray-600">Aucune demande de devis pour l'instant.</p>
        <a href="{{ route('client.insurance.create') }}" class="mt-3 inline-block text-emerald-700 underline">Comparer les offres</a>
    </div>
@endforelse
@endsection