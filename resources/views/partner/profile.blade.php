@extends('layouts.partner')
@section('title', 'Mon profil')

@section('content')
@php($input = 'mt-1 w-full rounded border border-gray-300 p-2')
@include('partner._header', ['partner' => $partner])

<div class="mt-6 rounded-lg bg-white p-6 shadow">
    <h2 class="font-semibold">Informations</h2>

    @if ($errors->any())
        <div class="mt-3 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('partner.profile.update') }}" class="mt-4 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium" for="company_name">Raison sociale *</label>
            <input id="company_name" name="company_name" value="{{ old('company_name', $partner->company_name) }}" required class="{{ $input }}">
        </div>

        <div>
            <label class="block text-sm font-medium" for="contact_phone">Téléphone professionnel *</label>
            <input id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $partner->contact_phone) }}" required class="{{ $input }}">
        </div>

        @if ($partner->type !== \App\Enums\PartnerType::ASSURANCE)
            <div class="rounded border border-gray-200 p-3">
                <p class="text-sm font-medium">Position</p>
                <p id="geo-status" class="mt-1 text-xs text-gray-500">
                    @if ($partner->hasBaseLocation())
                        Position enregistrée ({{ number_format($partner->base_lat, 4) }}, {{ number_format($partner->base_lng, 4) }}).
                    @else
                        Aucune position enregistrée : les distances ne peuvent pas être calculées.
                    @endif
                </p>
                <button type="button" id="geo-btn" class="mt-2 rounded border px-3 py-1.5 text-sm">Utiliser ma position actuelle</button>
                <input type="hidden" id="base_lat" name="base_lat" value="{{ old('base_lat', $partner->base_lat) }}">
                <input type="hidden" id="base_lng" name="base_lng" value="{{ old('base_lng', $partner->base_lng) }}">

                @if ($partner->type === \App\Enums\PartnerType::DEPANNEUR)
                    <label class="mt-3 block text-sm font-medium" for="radius_km">Rayon d'intervention (km)</label>
                    <input id="radius_km" name="radius_km" type="number" min="1" max="100" value="{{ old('radius_km', $partner->radius_km) }}" class="{{ $input }}">
                @endif
            </div>
        @endif

        <button class="rounded bg-emerald-700 px-4 py-2 font-medium text-white">Enregistrer</button>
    </form>
</div>

<div class="mt-6 rounded-lg bg-white p-6 shadow">
    <h2 class="font-semibold">Avis récents</h2>

    @forelse ($reviews as $review)
        <div class="mt-3 border-t pt-3 text-sm">
            <p>
                <span class="text-amber-600">{{ str_repeat('★', $review->score) }}{{ str_repeat('☆', 5 - $review->score) }}</span>
                <span class="text-gray-500">· {{ \Illuminate\Support\Str::before($review->user->name, ' ') }} · {{ $review->created_at->format('d/m/Y') }}</span>
            </p>
            @if ($review->comment) <p class="mt-1 text-gray-700">{{ $review->comment }}</p> @endif
        </div>
    @empty
        <p class="mt-2 text-sm text-gray-500">Aucun avis pour l'instant.</p>
    @endforelse
</div>

@if ($partner->type !== \App\Enums\PartnerType::ASSURANCE)
    <script>
        (function () {
            var btn = document.getElementById('geo-btn');
            if (!btn) { return; }
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
                    status.textContent = 'Position obtenue : cliquez sur Enregistrer pour la conserver.';
                }, function () {
                    status.textContent = 'Position non obtenue.';
                }, { enableHighAccuracy: true, timeout: 15000 });
            });
        })();
    </script>
@endif
@endsection