@extends('layouts.client')
@section('title', 'Coffre-fort')

@section('content')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">Coffre-fort numérique</h1>
    <a href="{{ route('client.documents.create') }}" class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">+ Ajouter</a>
</div>

<form method="GET" class="mt-4 flex flex-wrap gap-3 text-sm">
    <select name="type" class="rounded border border-gray-300 p-2">
        <option value="">Tous les types</option>
        @foreach ($types as $type)
            <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </select>
    <select name="vehicle" class="rounded border border-gray-300 p-2">
        <option value="">Tous les véhicules</option>
        @foreach ($vehicles as $v)
            <option value="{{ $v->id }}" @selected((int) request('vehicle') === $v->id)>{{ $v->plate }}</option>
        @endforeach
    </select>
    <button class="rounded border px-3 py-2">Filtrer</button>
    @if (request()->hasAny(['type', 'vehicle']))
        <a href="{{ route('client.documents.index') }}" class="self-center text-emerald-700 underline">Réinitialiser</a>
    @endif
</form>

@forelse ($documents as $doc)
    <div class="mt-4 rounded-lg bg-white p-4 shadow">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <p class="font-semibold">{{ $doc->title }}</p>
                <p class="text-sm text-gray-600">
                    {{ $doc->type->label() }}
                    @if ($doc->vehicle) · {{ $doc->vehicle->plate }} @endif
                    · {{ $doc->sizeForHumans() }}
                </p>
                @if ($doc->expires_at)
                    <p class="mt-1 text-sm">
                        @if ($doc->isExpired())
                            <span class="rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Expiré le {{ $doc->expires_at->format('d/m/Y') }}</span>
                        @elseif ($doc->expiresSoon())
                            <span class="rounded bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800">Expire le {{ $doc->expires_at->format('d/m/Y') }}</span>
                        @else
                            <span class="text-gray-500">Valide jusqu'au {{ $doc->expires_at->format('d/m/Y') }}</span>
                        @endif
                    </p>
                @endif
            </div>

            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('client.documents.show', $doc) }}" target="_blank" class="text-emerald-700 underline">Consulter</a>
                <a href="{{ route('client.documents.download', $doc) }}" class="text-emerald-700 underline">Télécharger</a>
                <form method="POST" action="{{ route('client.documents.destroy', $doc) }}" onsubmit="return confirm('Supprimer ce document ?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-red-700 underline">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
@empty
    <div class="mt-6 rounded-lg bg-white p-8 text-center shadow">
        <p class="text-gray-600">Aucun document pour l'instant.</p>
        <a href="{{ route('client.documents.create') }}" class="mt-3 inline-block text-emerald-700 underline">Ajouter mon premier document</a>
    </div>
@endforelse
@endsection