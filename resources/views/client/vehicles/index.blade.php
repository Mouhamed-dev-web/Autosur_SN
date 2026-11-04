@extends('layouts.client')
@section('title', 'Mes véhicules')

@section('content')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">Mes véhicules</h1>
    <a href="{{ route('client.vehicles.create') }}" class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">+ Ajouter</a>
</div>

    @forelse ($vehicles as $vehicle)
        <a href="{{ route('client.vehicles.show', $vehicle) }}" class="mt-4 block rounded-lg bg-white p-4 shadow hover:shadow-md">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-3">
        @if ($vehicle->photoUrl())
            <img src="{{ $vehicle->photoUrl() }}" alt="" class="h-14 w-20 rounded object-cover">
        @else
            <div class="flex h-14 w-20 items-center justify-center rounded bg-gray-100 text-2xl">🚗</div>
        @endif
        <div>
            <p class="text-lg font-semibold">{{ $vehicle->plate }}</p>
            <p class="text-sm text-gray-600">{{ $vehicle->brand }} {{ $vehicle->model }} · {{ $vehicle->year }}</p>
        </div>
    </div>
            <span class="rounded px-2 py-1 text-xs font-medium {{ $vehicle->insurance_status->color() }}">
                {{ $vehicle->insurance_status->label() }}
            </span>
        </div>
        @if ($vehicle->technical_visit_expires_at)
            <p class="mt-2 text-sm {{ $vehicle->technicalVisitDueSoon() ? 'text-orange-700' : 'text-gray-500' }}">
                Visite technique : échéance le {{ $vehicle->technical_visit_expires_at->format('d/m/Y') }}
            </p>
        @endif
    </a>
@empty
    <div class="mt-6 rounded-lg bg-white p-8 text-center shadow">
        <p class="text-gray-600">Vous n'avez pas encore de véhicule.</p>
        <a href="{{ route('client.vehicles.create') }}" class="mt-3 inline-block text-emerald-700 underline">Ajouter mon premier véhicule</a>
    </div>
@endforelse
@endsection