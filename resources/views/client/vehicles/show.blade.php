@extends('layouts.client')
@section('title', $vehicle->plate)

@section('content')
<div class="mx-auto max-w-2xl rounded-lg bg-white p-6 shadow">
    @if ($vehicle->photoUrl())
        <img src="{{ $vehicle->photoUrl() }}" alt="Photo {{ $vehicle->plate }}" class="mb-4 h-48 w-full rounded object-cover">
    @endif
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold">{{ $vehicle->plate }}</h1>
            <p class="text-gray-600">{{ $vehicle->brand }} {{ $vehicle->model }}</p>
        </div>
        <span class="rounded px-2 py-1 text-xs font-medium {{ $vehicle->insurance_status->color() }}">{{ $vehicle->insurance_status->label() }}</span>
    </div>

    <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
        <div><dt class="text-gray-500">Année</dt><dd>{{ $vehicle->year }}</dd></div>
        <div><dt class="text-gray-500">Couleur</dt><dd>{{ $vehicle->color ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">N° de châssis</dt><dd>{{ $vehicle->vin ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">Expiration assurance</dt><dd>{{ $vehicle->insurance_expires_at?->format('d/m/Y') ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">Dernière visite technique</dt><dd>{{ $vehicle->technical_visit_last_at?->format('d/m/Y') ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">Prochaine échéance</dt><dd>{{ $vehicle->technical_visit_expires_at?->format('d/m/Y') ?? '—' }}</dd></div>
    </dl>
    <div class="mt-6 border-t pt-4">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold">Documents liés</h2>
            <a href="{{ route('client.documents.create', ['vehicle' => $vehicle->id]) }}" class="text-sm text-emerald-700 underline">+ Ajouter</a>
        </div>
        @forelse ($vehicle->documents as $doc)
            <p class="mt-2 text-sm">
                <a href="{{ route('client.documents.show', $doc) }}" target="_blank" class="text-emerald-700 underline">{{ $doc->title }}</a>
                <span class="text-gray-500">· {{ $doc->type->label() }}</span>
            </p>
        @empty
            <p class="mt-2 text-sm text-gray-500">Aucun document lié.</p>
        @endforelse
    </div>

    <div class="mt-6 border-t pt-4">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold">Historique des visites techniques</h2>
            <a href="{{ route('client.visits.create', ['vehicle' => $vehicle->id]) }}" class="text-sm text-emerald-700 underline">+ Rendez-vous</a>
        </div>
        @forelse ($vehicle->technicalVisits()->latest('scheduled_at')->limit(5)->get() as $visit)
            <p class="mt-2 text-sm">
                {{ $visit->scheduled_at->format('d/m/Y H\hi') }}
                <span class="ml-1 rounded px-2 py-0.5 text-xs font-medium {{ $visit->status->color() }}">{{ $visit->status->label() }}</span>
            </p>
        @empty
            <p class="mt-2 text-sm text-gray-500">Aucune visite enregistrée.</p>
        @endforelse
    </div>

    <div class="mt-6 flex gap-3">
        <a href="{{ route('client.vehicles.edit', $vehicle) }}" class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">Modifier</a>
        <form method="POST" action="{{ route('client.vehicles.destroy', $vehicle) }}"
              onsubmit="return confirm('Supprimer ce véhicule ?')">
            @csrf
            @method('DELETE')
            <button class="rounded border border-red-300 px-4 py-2 text-sm text-red-700">Supprimer</button>
        </form>
        <a href="{{ route('client.vehicles.index') }}" class="rounded border px-4 py-2 text-sm">Retour</a>
    </div>
</div>
@endsection