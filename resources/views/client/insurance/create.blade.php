@extends('layouts.client')
@section('title', 'Demande de devis')

@section('content')
@php($input = 'mt-1 w-full rounded border border-gray-300 p-2')
<div class="mx-auto max-w-xl rounded-lg bg-white p-6 shadow" x-data="{ cover: '{{ old('cover_type', 'tiers') }}' }">
    <h1 class="text-2xl font-bold">Demande de devis assurance</h1>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    @if ($vehicles->isEmpty())
        <p class="mt-4 text-gray-600">Ajoutez d'abord un véhicule.
            <a href="{{ route('client.vehicles.create') }}" class="text-emerald-700 underline">Ajouter un véhicule</a></p>
    @else
        <form method="POST" action="{{ route('client.insurance.store') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium" for="vehicle_id">Véhicule *</label>
                <select id="vehicle_id" name="vehicle_id" required class="{{ $input }}">
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}" @selected((int) old('vehicle_id', $selectedVehicle) === $v->id)>{{ $v->plate }} ({{ $v->brand }} {{ $v->model }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium" for="cover_type">Couverture *</label>
                <select id="cover_type" name="cover_type" x-model="cover" class="{{ $input }}">
                    @foreach ($covers as $cover)
                        <option value="{{ $cover->value }}">{{ $cover->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium" for="fiscal_power">Puissance fiscale (CV) *</label>
                    <input id="fiscal_power" name="fiscal_power" type="number" min="1" max="50" value="{{ old('fiscal_power') }}" required class="{{ $input }}">
                    <p class="mt-1 text-xs text-gray-500">Indiquée sur la carte grise.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="duration_months">Durée *</label>
                    <select id="duration_months" name="duration_months" class="{{ $input }}">
                        @foreach ([3, 6, 12] as $m)
                            <option value="{{ $m }}" @selected((int) old('duration_months', 12) === $m)>{{ $m }} mois</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div x-show="cover === 'tous_risques'" x-cloak>
                <label class="block text-sm font-medium" for="vehicle_value">Valeur du véhicule (FCFA) *</label>
                <input id="vehicle_value" name="vehicle_value" type="number" min="100000" step="50000" value="{{ old('vehicle_value') }}" class="{{ $input }}">
            </div>

            <div class="flex gap-3">
                <button class="rounded bg-emerald-700 px-4 py-2 font-medium text-white">Voir les offres</button>
                <a href="{{ route('client.insurance.index') }}" class="rounded border px-4 py-2">Annuler</a>
            </div>
        </form>
    @endif
</div>
@endsection