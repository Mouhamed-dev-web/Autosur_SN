@extends('layouts.auth')
@section('title', 'Devenir partenaire')
@section('card_class', 'auth-card--wide')

@section('content')
<h1 class="text-2xl font-bold text-gray-900">Devenir partenaire</h1>
<p class="mt-1 text-sm text-gray-500">Garages, dépanneurs et assureurs : créez votre compte. Votre dossier est examiné par AutoSur.sn avant activation.</p>

<form method="POST" action="{{ route('partners.register.store') }}" class="mt-6 space-y-4">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700" for="type">Vous êtes</label>
        <select id="type" name="type" class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 py-2.5 pl-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            <option value="garage" @selected(old('type') === 'garage')>🔧 Garage / centre de contrôle</option>
            <option value="depanneur" @selected(old('type') === 'depanneur')>🚛 Dépanneur</option>
            <option value="assurance" @selected(old('type') === 'assurance')>🛡️ Compagnie d'assurance</option>
        </select>
    </div>

    <x-auth.field name="company_name" label="Raison sociale" icon="🏢" required />
    <x-auth.field name="contact_phone" label="Téléphone professionnel (affiché aux clients)" type="tel" icon="☎️" required placeholder="77 123 45 67" />

    <h2 class="pt-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Responsable du compte</h2>
    <x-auth.field name="name" label="Nom complet" icon="👤" required />

    <div class="grid gap-4 sm:grid-cols-2">
        <x-auth.field name="phone" label="Téléphone (code SMS)" type="tel" icon="📱" required placeholder="77 123 45 67" />
        <x-auth.field name="email" label="E-mail" type="email" icon="✉️" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-auth.field name="password" label="Mot de passe" type="password" icon="🔒" required hint="8 caractères minimum" />
        <x-auth.field name="password_confirmation" label="Confirmer" type="password" icon="🔒" required />
    </div>

   @include('partials.location-picker', ['lat' => old('base_lat'), 'lng' => old('base_lng')])

    <div>
        <label class="block text-sm font-medium text-gray-700" for="radius_km">Rayon d'intervention en km (dépanneurs)</label>
        <input id="radius_km" name="radius_km" type="number" min="1" max="100" value="{{ old('radius_km', 30) }}"
            class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm">
    </div>

    <label class="flex items-start gap-2 text-sm text-gray-700">
        <input type="checkbox" name="terms" value="1" class="mt-1 rounded border-gray-300" @checked(old('terms'))>
        J'accepte que mon dossier soit examiné par AutoSur.sn avant l'activation de mon compte.
    </label>

    <button class="w-full rounded-lg bg-emerald-700 py-3 font-semibold text-white shadow hover:bg-emerald-800">Créer mon compte partenaire</button>
</form>

<p class="mt-5 text-center text-sm text-gray-600">
    Déjà partenaire ? <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:underline">Connexion</a>
</p>

<script>
    (function () {
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