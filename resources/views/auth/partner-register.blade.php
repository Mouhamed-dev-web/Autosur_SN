@extends('layouts.guest')
@section('title', 'Devenir partenaire')

@section('content')
@php($input = 'mt-1 w-full rounded border border-gray-300 p-2')
<div class="mx-auto my-8 max-w-xl rounded-2xl bg-white/95 p-6 shadow-lg sm:p-8">
    <h1 class="text-2xl font-bold text-gray-900">Devenir partenaire</h1>
    <p class="mt-1 text-sm text-gray-600">
        Garages, dépanneurs et assureurs : créez votre compte professionnel. Votre dossier est examiné par AutoSur.sn avant activation.
    </p>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('partners.register.store') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium" for="type">Vous êtes *</label>
            <select id="type" name="type" class="{{ $input }}">
                <option value="garage" @selected(old('type') === 'garage')>Garage / centre de contrôle</option>
                <option value="depanneur" @selected(old('type') === 'depanneur')>Dépanneur</option>
                <option value="assurance" @selected(old('type') === 'assurance')>Compagnie d'assurance</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium" for="company_name">Raison sociale *</label>
            <input id="company_name" name="company_name" value="{{ old('company_name') }}" required class="{{ $input }}">
        </div>

        <div>
            <label class="block text-sm font-medium" for="contact_phone">Téléphone professionnel (affiché aux clients) *</label>
            <input id="contact_phone" name="contact_phone" type="tel" placeholder="77 123 45 67" value="{{ old('contact_phone') }}" required class="{{ $input }}">
        </div>

        <h2 class="pt-2 font-semibold">Responsable du compte</h2>

        <div>
            <label class="block text-sm font-medium" for="name">Nom complet *</label>
            <input id="name" name="name" value="{{ old('name') }}" required class="{{ $input }}">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="phone">Téléphone (code SMS) *</label>
                <input id="phone" name="phone" type="tel" placeholder="77 123 45 67" value="{{ old('phone') }}" required class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium" for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="{{ $input }}">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="password">Mot de passe (8 caractères min.) *</label>
                <input id="password" name="password" type="password" required class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium" for="password_confirmation">Confirmer *</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="{{ $input }}">
            </div>
        </div>

        <div class="rounded border border-gray-200 p-3">
            <p class="text-sm font-medium">Localisation (garages et dépanneurs)</p>
            <p id="geo-status" class="mt-1 text-xs text-gray-500">Facultatif : elle sert à calculer les distances.</p>
            <button type="button" id="geo-btn" class="mt-2 rounded border px-3 py-1.5 text-sm">Utiliser ma position actuelle</button>
            <input type="hidden" id="base_lat" name="base_lat" value="{{ old('base_lat') }}">
            <input type="hidden" id="base_lng" name="base_lng" value="{{ old('base_lng') }}">

            <label class="mt-3 block text-sm font-medium" for="radius_km">Rayon d'intervention en km (dépanneurs)</label>
            <input id="radius_km" name="radius_km" type="number" min="1" max="100" value="{{ old('radius_km', 30) }}" class="{{ $input }}">
        </div>

        <label class="flex items-start gap-2 text-sm text-gray-700">
            <input type="checkbox" name="terms" value="1" class="mt-1" @checked(old('terms'))>
            J'accepte que mon dossier soit examiné par AutoSur.sn avant l'activation de mon compte.
        </label>

        <button class="w-full rounded-xl bg-emerald-700 px-4 py-3 font-semibold text-white hover:bg-emerald-800">Créer mon compte partenaire</button>
    </form>
</div>

<script>
    (function () {
        var btn = document.getElementById('geo-btn');
        var lat = document.getElementById('base_lat');
        var lng = document.getElementById('base_lng');
        var status = document.getElementById('geo-status');

        btn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                status.textContent = 'Géolocalisation indisponible sur cet appareil.';
                return;
            }
            status.textContent = 'Localisation en cours...';
            navigator.geolocation.getCurrentPosition(function (p) {
                lat.value = p.coords.latitude.toFixed(7);
                lng.value = p.coords.longitude.toFixed(7);
                status.textContent = 'Position enregistrée.';
            }, function () {
                status.textContent = 'Position non obtenue. Vous pourrez la renseigner plus tard dans votre profil.';
            }, { enableHighAccuracy: true, timeout: 15000 });
        });
    })();
</script>
@endsection