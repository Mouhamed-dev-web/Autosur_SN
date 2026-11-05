@extends('layouts.partner')
@section('title', 'Demandes SOS')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold">{{ $partner->company_name }}</h1>
        <p class="text-sm text-gray-600">Rayon d'intervention : {{ $partner->radius_km }} km</p>
    </div>

    @if ($partner->is_validated)
        <form method="POST" action="{{ route('partner.sos.toggle') }}">
            @csrf
            <button class="rounded px-4 py-2 text-sm font-medium text-white {{ $partner->is_available ? 'bg-emerald-700' : 'bg-gray-500' }}">
                {{ $partner->is_available ? 'Disponible (cliquer pour passer indisponible)' : 'Indisponible (cliquer pour passer disponible)' }}
            </button>
        </form>
    @endif
</div>

@unless ($partner->is_validated)
    <div class="mt-6 rounded bg-orange-50 p-4 text-sm text-orange-800">
        Votre compte est en attente de validation par AutoSur.sn. Vous pourrez recevoir des demandes dès sa validation.
    </div>
@endunless

@if ($current)
    <a href="{{ route('partner.sos.show', $current) }}" class="mt-6 block rounded-lg border-l-4 border-blue-600 bg-white p-4 shadow">
        <p class="text-sm text-gray-500">Intervention en cours</p>
        <p class="font-semibold">{{ $current->problem->label() }}
            @if ($current->vehicle) · {{ $current->vehicle->brand }} {{ $current->vehicle->model }} @endif
        </p>
        <span class="mt-1 inline-block rounded px-2 py-0.5 text-xs font-medium {{ $current->status->color() }}">{{ $current->status->label() }}</span>
    </a>
@elseif ($partner->is_validated && $partner->is_available)
    <h2 class="mt-6 font-semibold">Demandes proches ({{ $available->count() }})</h2>

    @forelse ($available as $row)
        @php($sos = $row['request'])
        <div class="mt-3 rounded-lg bg-white p-4 shadow">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <p class="font-semibold">{{ $sos->problem->label() }}</p>
                    @if ($sos->vehicle)
                        <p class="text-sm text-gray-600">{{ $sos->vehicle->brand }} {{ $sos->vehicle->model }}</p>
                    @endif
                    @if ($sos->description) <p class="mt-1 text-sm text-gray-600">{{ $sos->description }}</p> @endif
                    @if ($sos->location_text) <p class="text-sm text-gray-600">Lieu : {{ $sos->location_text }}</p> @endif
                    <p class="mt-1 text-xs text-gray-500">Il y a {{ $sos->created_at->diffForHumans(null, true) }}</p>
                </div>
                <p class="text-right text-lg font-bold text-emerald-700">
                    {{ $row['distance'] !== null ? $row['distance'].' km' : 'Distance inconnue' }}
                </p>
            </div>

            <div class="mt-3 flex gap-3">
                <form method="POST" action="{{ route('partner.sos.accept', $sos) }}">
                    @csrf
                    <button class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">Accepter</button>
                </form>
                <form method="POST" action="{{ route('partner.sos.decline', $sos) }}">
                    @csrf
                    <button class="rounded border px-4 py-2 text-sm">Refuser</button>
                </form>
            </div>
        </div>
    @empty
        <div class="mt-3 rounded-lg bg-white p-6 text-center text-gray-600 shadow">
            Aucune demande pour l'instant. Cette page se met à jour toute seule.
        </div>
    @endforelse

    <script>
        (function () {
            var current = @json($available->pluck('request.id')->values());
            setInterval(function () {
                fetch(@json(route('partner.sos.available')), { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (d) {
                        if (d && JSON.stringify(d.ids) !== JSON.stringify(current)) { location.reload(); }
                    })
                    .catch(function () {});
            }, 10000);
        })();
    </script>
@endif
@endsection