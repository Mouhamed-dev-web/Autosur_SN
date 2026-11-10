@extends('layouts.partner')
@section('title', 'Rendez-vous')

@section('content')
@include('partner._header', ['partner' => $partner])

@if ($partner->is_validated)
    <h2 class="mt-8 font-semibold">Mes rendez-vous ({{ $mine->count() }})</h2>

    @forelse ($mine as $visit)
        <div class="mt-3 rounded-lg bg-white p-4 shadow">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <p class="font-semibold">{{ $visit->vehicle->plate }}
                        <span class="font-normal text-gray-600">· {{ $visit->vehicle->brand }} {{ $visit->vehicle->model }} {{ $visit->vehicle->year }}</span>
                    </p>
                    <p class="text-sm text-gray-700">{{ $visit->scheduled_at->translatedFormat('l d/m/Y à H\hi') }}</p>
                    @if ($visit->notes) <p class="mt-1 text-sm text-gray-600">Remarque : {{ $visit->notes }}</p> @endif

                    {{-- coordonnées du client : seulement après confirmation --}}
                    @if ($visit->status === \App\Enums\VisitStatus::CONFIRMED)
                        <p class="mt-2 text-sm">Client : <strong>{{ $visit->user->name }}</strong>
                            @if ($visit->user->phone)
                                · <a href="tel:{{ $visit->user->phone }}" class="font-bold text-blue-800 underline">{{ $visit->user->phone }}</a>
                            @endif
                        </p>
                    @endif
                </div>
                <span class="rounded px-2 py-1 text-xs font-medium {{ $visit->status->color() }}">{{ $visit->status->label() }}</span>
            </div>

            <div class="mt-3 flex flex-wrap gap-3">
                @if ($visit->status === \App\Enums\VisitStatus::REQUESTED)
                    <form method="POST" action="{{ route('partner.visits.confirm', $visit) }}">
                        @csrf
                        <button class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">Confirmer</button>
                    </form>
                @endif

                @if ($visit->status === \App\Enums\VisitStatus::CONFIRMED)
                    <form method="POST" action="{{ route('partner.visits.complete', $visit) }}">
                        @csrf
                        <button class="rounded bg-indigo-700 px-4 py-2 text-sm font-medium text-white">Visite effectuée</button>
                    </form>
                @endif

                <form method="POST" action="{{ route('partner.visits.reject', $visit) }}" onsubmit="return confirm('Refuser ce rendez-vous ?')">
                    @csrf
                    <button class="rounded border border-red-300 px-4 py-2 text-sm text-red-700">Refuser</button>
                </form>
            </div>
        </div>
    @empty
        <div class="mt-3 rounded-lg bg-white p-6 text-center text-gray-600 shadow">Aucun rendez-vous à traiter.</div>
    @endforelse

    <h2 class="mt-8 font-semibold">Demandes disponibles ({{ $available->count() }})</h2>
    <p class="text-sm text-gray-500">Demandes sans garage choisi : le premier qui confirme l'obtient.</p>

    @forelse ($available as $visit)
        <div class="mt-3 rounded-lg bg-white p-4 shadow">
            <p class="font-semibold">{{ $visit->vehicle->brand }} {{ $visit->vehicle->model }} {{ $visit->vehicle->year }}
                <span class="font-normal text-gray-600">· {{ $visit->vehicle->plate }}</span></p>
            <p class="text-sm text-gray-700">{{ $visit->scheduled_at->translatedFormat('l d/m/Y à H\hi') }}</p>
            @if ($visit->notes) <p class="mt-1 text-sm text-gray-600">Remarque : {{ $visit->notes }}</p> @endif

            <form method="POST" action="{{ route('partner.visits.confirm', $visit) }}" class="mt-3">
                @csrf
                <button class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">Prendre ce rendez-vous</button>
            </form>
        </div>
    @empty
        <div class="mt-3 rounded-lg bg-white p-6 text-center text-gray-600 shadow">Aucune demande en attente.</div>
    @endforelse

    @if ($history->isNotEmpty())
        <h2 class="mt-8 font-semibold">Historique récent</h2>
        @foreach ($history as $visit)
            <div class="mt-2 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white p-3 text-sm shadow">
                <span>{{ $visit->vehicle->plate }} · {{ $visit->scheduled_at->format('d/m/Y') }}</span>
                <span class="flex items-center gap-2">
                    @if ($visit->rating)
                        <span class="text-amber-600">{{ str_repeat('★', $visit->rating->score) }}</span>
                    @endif
                    <span class="rounded px-2 py-0.5 text-xs font-medium {{ $visit->status->color() }}">{{ $visit->status->label() }}</span>
                </span>
            </div>
        @endforeach
    @endif
@endif
@endsection