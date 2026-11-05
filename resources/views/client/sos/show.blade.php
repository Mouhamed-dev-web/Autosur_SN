@extends('layouts.client')
@section('title', 'Suivi SOS')

@php
    use App\Enums\SosStatus;

    $showPartner = $sos->partner && in_array(
        $sos->status,
        [SosStatus::ACCEPTED, SosStatus::ARRIVED, SosStatus::COMPLETED],
        true
    );
@endphp

@section('content')
<div class="mx-auto max-w-xl">
    <a href="{{ route('client.sos.index') }}" class="text-sm text-emerald-700 underline">← Mes demandes SOS</a>

    @if ($errors->any())
        <div class="mt-3 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="mt-3 rounded-lg bg-white p-6 shadow">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="text-xl font-bold">{{ $sos->problem->label() }}</h1>
                @if ($sos->vehicle)
                    <p class="text-sm text-gray-600">
                        {{ $sos->vehicle->plate }} · {{ $sos->vehicle->brand }} {{ $sos->vehicle->model }}
                    </p>
                @endif
            </div>
            <span class="rounded px-2 py-1 text-sm font-medium {{ $sos->status->color() }}">
                {{ $sos->status->label() }}
            </span>
        </div>

        @if ($sos->status === SosStatus::PENDING)
            <p class="mt-4 text-sm text-gray-700">
                Votre demande est diffusée aux dépanneurs proches. Gardez cette page ouverte : elle se met à jour toute seule.
            </p>
            @if ($sos->isStale())
                <div class="mt-3 rounded bg-orange-50 p-3 text-sm text-orange-800">
                    Aucun dépanneur n'a répondu depuis plus d'une heure. Annulez et refaites une demande, ou appelez un dépanneur directement.
                </div>
            @endif
        @endif

        @if ($showPartner)
            <div class="mt-4 rounded bg-blue-50 p-4">
                <p class="text-sm text-gray-600">Votre dépanneur</p>
                <p class="font-semibold">{{ $sos->partner->company_name }}</p>
                <a href="tel:{{ $sos->partner->contact_phone }}" class="mt-1 inline-block text-lg font-bold text-blue-800 underline">
                    {{ $sos->partner->contact_phone }}
                </a>
                @if ($sos->accepted_at)
                    <p class="mt-1 text-xs text-gray-500">Accepté à {{ $sos->accepted_at->format('H\hi') }}</p>
                @endif
            </div>
        @endif

        <div class="mt-4 text-sm text-gray-700">
            @if ($sos->description)
                <p>Précisions : {{ $sos->description }}</p>
            @endif
            @if ($sos->location_text)
                <p>Lieu indiqué : {{ $sos->location_text }}</p>
            @endif
        </div>

        @if ($sos->hasCoordinates())
            @include('partials.sos-map', ['lat' => $sos->lat, 'lng' => $sos->lng])
        @endif

        @if ($sos->canBeCancelled())
            <form method="POST" action="{{ route('client.sos.cancel', $sos) }}" class="mt-6"
                  onsubmit="return confirm('Annuler cette demande SOS ?')">
                @csrf
                <button class="rounded border border-red-300 px-4 py-2 text-sm text-red-700">Annuler la demande</button>
            </form>
        @endif
    </div>
</div>

@if ($sos->status->isOpen())
    <div id="sos-watch"
         data-url="{{ route('client.sos.status', $sos) }}"
         data-status="{{ $sos->status->value }}"
         hidden></div>

    <script>
        (function () {
            var el = document.getElementById('sos-watch');
            if (!el) { return; }

            var url = el.dataset.url;
            var current = el.dataset.status;

            setInterval(function () {
                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (d) {
                        if (d && d.status !== current) { location.reload(); }
                    })
                    .catch(function () {});
            }, 8000);
        })();
    </script>
@endif
@endsection