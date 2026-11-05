@extends('layouts.client')
@section('title', 'SOS Panne')

@section('content')
@php($input = 'mt-1 w-full rounded border border-gray-300 p-2')
<div class="mx-auto max-w-xl rounded-lg bg-white p-6 shadow">
    <h1 class="text-2xl font-bold text-red-700">SOS Panne</h1>

    <div class="mt-3 rounded bg-red-50 p-3 text-sm text-red-800">
        Accident avec blessés ou danger immédiat : appelez d'abord les secours
        (police 17, sapeurs-pompiers 18, SAMU 1515).
    </div>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    @if ($vehicles->isEmpty())
        <p class="mt-4 text-gray-600">Ajoutez d'abord un véhicule.
            <a href="{{ route('client.vehicles.create') }}" class="text-emerald-700 underline">Ajouter un véhicule</a></p>
    @else
        <form method="POST" action="{{ route('client.sos.store') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium" for="vehicle_id">Véhicule en panne *</label>
                <select id="vehicle_id" name="vehicle_id" required class="{{ $input }}">
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}" @selected((int) old('vehicle_id') === $v->id)>{{ $v->plate }} ({{ $v->brand }} {{ $v->model }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium" for="problem">Problème *</label>
                <select id="problem" name="problem" required class="{{ $input }}">
                    @foreach ($problems as $problem)
                        <option value="{{ $problem->value }}" @selected(old('problem') === $problem->value)>{{ $problem->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium" for="description">Précisions (facultatif)</label>
                <textarea id="description" name="description" rows="2" maxlength="500" class="{{ $input }}">{{ old('description') }}</textarea>
            </div>

            <div class="rounded border border-gray-200 p-3">
                <p class="text-sm font-medium">Où êtes-vous ?</p>
                <p id="geo-status" class="mt-1 text-sm text-gray-600">Localisation en cours...</p>
                <button type="button" id="geo-btn" class="mt-2 rounded border px-3 py-1.5 text-sm">Me géolocaliser</button>

                <input type="hidden" id="lat" name="lat" value="{{ old('lat') }}">
                <input type="hidden" id="lng" name="lng" value="{{ old('lng') }}">

                <label class="mt-3 block text-sm font-medium" for="location_text">Lieu (quartier, route, repère)</label>
                <input id="location_text" name="location_text" value="{{ old('location_text') }}" class="{{ $input }}"
                       placeholder="Ex. : VDN, devant la station Total">
            </div>

            <div class="flex gap-3">
                <button class="rounded bg-red-700 px-4 py-2 font-medium text-white">Demander un dépanneur</button>
                <a href="{{ route('client.sos.index') }}" class="rounded border px-4 py-2">Annuler</a>
            </div>
        </form>

        <script>
            (function () {
                var lat = document.getElementById('lat');
                var lng = document.getElementById('lng');
                var status = document.getElementById('geo-status');

                function locate() {
                    if (!navigator.geolocation) {
                        status.textContent = "Géolocalisation indisponible : décrivez où vous êtes.";
                        return;
                    }
                    status.textContent = "Localisation en cours...";
                    navigator.geolocation.getCurrentPosition(function (p) {
                        lat.value = p.coords.latitude.toFixed(7);
                        lng.value = p.coords.longitude.toFixed(7);
                        status.textContent = "Position trouvée (précision d'environ " + Math.round(p.coords.accuracy) + " m).";
                    }, function () {
                        lat.value = '';
                        lng.value = '';
                        status.textContent = "Position non obtenue. Décrivez où vous êtes ci-dessous.";
                    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 });
                }

                document.getElementById('geo-btn').addEventListener('click', locate);
                locate();
            })();
        </script>
    @endif
</div>
@endsection