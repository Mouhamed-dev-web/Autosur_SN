@extends('layouts.partner')
@section('title', 'Intervention SOS')

@section('content')
<a href="{{ route('partner.sos.index') }}" class="text-sm text-emerald-700 underline">← Demandes SOS</a>

<div class="mt-3 rounded-lg bg-white p-6 shadow">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <h1 class="text-xl font-bold">{{ $sos->problem->label() }}</h1>
        <span class="rounded px-2 py-1 text-sm font-medium {{ $sos->status->color() }}">{{ $sos->status->label() }}</span>
    </div>

    @if ($sos->status === \App\Enums\SosStatus::CANCELLED)
        <div class="mt-3 rounded bg-gray-100 p-3 text-sm text-gray-700">Le client a annulé cette demande.</div>
    @endif

    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
        <div>
            <dt class="text-gray-500">Client</dt>
            <dd class="font-medium">{{ $sos->user->name }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Téléphone</dt>
            <dd><a href="tel:{{ $sos->user->phone }}" class="text-lg font-bold text-blue-800 underline">{{ $sos->user->phone }}</a></dd>
        </div>
        @if ($sos->vehicle)
            <div>
                <dt class="text-gray-500">Véhicule</dt>
                <dd>{{ $sos->vehicle->brand }} {{ $sos->vehicle->model }}{{ $sos->vehicle->color ? ' · '.$sos->vehicle->color : '' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Immatriculation</dt>
                <dd>{{ $sos->vehicle->plate }}</dd>
            </div>
        @endif
        @if ($sos->location_text)
            <div class="sm:col-span-2"><dt class="text-gray-500">Lieu indiqué</dt><dd>{{ $sos->location_text }}</dd></div>
        @endif
        @if ($sos->description)
            <div class="sm:col-span-2"><dt class="text-gray-500">Précisions</dt><dd>{{ $sos->description }}</dd></div>
        @endif
    </dl>

    @if ($sos->hasCoordinates())
        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $sos->lat }},{{ $sos->lng }}" target="_blank" rel="noopener"
           class="mt-4 inline-block rounded border px-3 py-1.5 text-sm text-emerald-700 underline">Ouvrir l'itinéraire</a>
        @include('partials.sos-map', ['lat' => $sos->lat, 'lng' => $sos->lng])
    @endif

    <div class="mt-6 flex gap-3">
        @if ($sos->status === \App\Enums\SosStatus::ACCEPTED)
            <form method="POST" action="{{ route('partner.sos.arrive', $sos) }}">
                @csrf
                <button class="rounded bg-indigo-700 px-4 py-2 text-sm font-medium text-white">Je suis arrivé</button>
            </form>
        @elseif ($sos->status === \App\Enums\SosStatus::ARRIVED)
            <form method="POST" action="{{ route('partner.sos.complete', $sos) }}">
                @csrf
                <button class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">Intervention terminée</button>
            </form>
        @endif
    </div>
</div>
@endsection