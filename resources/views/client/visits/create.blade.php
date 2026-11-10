@extends('layouts.client')
@section('title', 'Prendre rendez-vous')

@section('content')
@php($input = 'mt-1 w-full rounded border border-gray-300 p-2')
<div class="mx-auto max-w-xl rounded-lg bg-white p-6 shadow">
    <h1 class="text-2xl font-bold">Rendez-vous de visite technique</h1>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    @if ($vehicles->isEmpty())
        <p class="mt-4 text-gray-600">Ajoutez d'abord un véhicule.
            <a href="{{ route('client.vehicles.create') }}" class="text-emerald-700 underline">Ajouter un véhicule</a></p>
    @else
        <form method="POST" action="{{ route('client.visits.store') }}" class="mt-6 space-y-4">
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
                <label class="block text-sm font-medium" for="partner_id">Garage / centre de contrôle</label>
                <select id="partner_id" name="partner_id" class="{{ $input }}">
                    <option value="">Peu importe : le premier garage disponible</option>
                    @foreach ($garages as $garage)
                        <option value="{{ $garage->id }}" @selected((int) old('partner_id') === $garage->id)>{{ $garage->company_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium" for="scheduled_at">Date et heure souhaitées *</label>
                <input id="scheduled_at" name="scheduled_at" type="datetime-local" step="1800"
                       min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                       value="{{ old('scheduled_at') }}" required class="{{ $input }}">
                <p class="mt-1 text-xs text-gray-500">Du lundi au samedi, de 8h à 17h, par créneaux de 30 minutes (8h00, 8h30, 9h00...).</p>
            </div>

            <div>
                <label class="block text-sm font-medium" for="notes">Remarque (facultatif)</label>
                <textarea id="notes" name="notes" rows="2" maxlength="500" class="{{ $input }}">{{ old('notes') }}</textarea>
            </div>

            <div class="rounded bg-emerald-50 p-3 text-sm text-emerald-900">
                Frais de service AutoSur.sn : <strong>{{ number_format($fee, 0, ',', ' ') }} FCFA</strong>.
                Le paiement en ligne (Wave, Orange Money) sera proposé prochainement.
            </div>

            <div class="flex gap-3">
                <button class="rounded bg-emerald-700 px-4 py-2 font-medium text-white">Envoyer la demande</button>
                <a href="{{ route('client.visits.index') }}" class="rounded border px-4 py-2">Annuler</a>
            </div>
        </form>
    @endif
</div>
@endsection