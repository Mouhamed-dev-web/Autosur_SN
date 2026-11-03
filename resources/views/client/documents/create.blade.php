@extends('layouts.client')
@section('title', 'Ajouter un document')

@section('content')
@php($input = 'mt-1 w-full rounded border border-gray-300 p-2')
<div class="mx-auto max-w-xl rounded-lg bg-white p-6 shadow">
    <h1 class="text-2xl font-bold">Ajouter un document</h1>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('client.documents.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium" for="file">Fichier (PDF, JPG, PNG, 5 Mo max) *</label>
            <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/png,image/jpeg" required class="mt-1 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium" for="title">Titre *</label>
            <input id="title" name="title" value="{{ old('title') }}" required class="{{ $input }}" placeholder="Carte grise Corolla">
        </div>

        <div>
            <label class="block text-sm font-medium" for="type">Type *</label>
            <select id="type" name="type" class="{{ $input }}">
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium" for="vehicle_id">Véhicule concerné</label>
            <select id="vehicle_id" name="vehicle_id" class="{{ $input }}">
                <option value="">Aucun</option>
                @foreach ($vehicles as $v)
                    <option value="{{ $v->id }}" @selected((int) old('vehicle_id', $selectedVehicle) === $v->id)>{{ $v->plate }} ({{ $v->brand }} {{ $v->model }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium" for="expires_at">Date d'expiration</label>
            <input id="expires_at" name="expires_at" type="date" value="{{ old('expires_at') }}" class="{{ $input }}">
        </div>

        <div class="flex gap-3">
            <button class="rounded bg-emerald-700 px-4 py-2 font-medium text-white">Enregistrer</button>
            <a href="{{ route('client.documents.index') }}" class="rounded border px-4 py-2">Annuler</a>
        </div>
    </form>
</div>
@endsection