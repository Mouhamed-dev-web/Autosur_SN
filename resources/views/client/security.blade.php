@extends('layouts.client')
@section('title', 'Sécurité')

@section('content')
@php
    $input = 'mt-1 w-full rounded border border-gray-300 p-2';
@endphp
<div class="mx-auto max-w-xl space-y-6">
    <h1 class="text-2xl font-bold">Sécurité du compte</h1>

    @if ($errors->any())
        <div class="rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="font-semibold">Changer mon mot de passe</h2>
        <form method="POST" action="{{ route('client.security.password') }}" class="mt-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium" for="current_password">Mot de passe actuel</label>
                <input id="current_password" name="current_password" type="password" required class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium" for="password">Nouveau mot de passe (8 caractères min.)</label>
                <input id="password" name="password" type="password" required class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium" for="password_confirmation">Confirmer</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="{{ $input }}">
            </div>
            <button class="rounded bg-emerald-700 px-4 py-2 font-medium text-white">Changer le mot de passe</button>
            <p class="text-xs text-gray-500">Vos autres appareils seront déconnectés.</p>
        </form>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="font-semibold">Numéro de téléphone</h2>
        <p class="mt-1 text-sm text-gray-600">Numéro actuel : <strong>{{ $user->phone ?? 'non renseigné' }}</strong></p>

        @if ($pendingPhone)
            <form method="POST" action="{{ route('client.security.phone.confirm') }}" class="mt-4 space-y-3">
                @csrf
                <p class="text-sm">Saisissez le code envoyé au <strong>{{ $pendingPhone }}</strong>.</p>
                <input name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required
                       class="{{ $input }} text-center text-xl tracking-widest">
                <button class="rounded bg-emerald-700 px-4 py-2 font-medium text-white">Valider le nouveau numéro</button>
            </form>
            <form method="POST" action="{{ route('client.security.phone.cancel') }}" class="mt-2">
                @csrf
                <button class="text-sm text-gray-600 underline">Annuler le changement</button>
            </form>
        @else
            <form method="POST" action="{{ route('client.security.phone.request') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium" for="new_phone">Nouveau numéro</label>
                    <input id="new_phone" name="new_phone" type="tel" placeholder="77 123 45 67" value="{{ old('new_phone') }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-sm font-medium" for="phone_password">Mot de passe actuel</label>
                    <input id="phone_password" name="current_password" type="password" required class="{{ $input }}">
                </div>
                <button class="rounded border border-emerald-700 px-4 py-2 font-medium text-emerald-700">Recevoir un code</button>
            </form>
        @endif
        <div class="rounded-lg border border-red-200 bg-white p-6 shadow">
            <h2 class="font-semibold text-red-700">Supprimer mon compte</h2>
            <p class="mt-1 text-sm text-gray-600">Efface vos données de façon définitive.</p>
            <a href="{{ route('client.account.delete') }}" class="mt-3 inline-block rounded border border-red-300 px-4 py-2 text-sm text-red-700">Supprimer mon compte</a>
        </div>
    </div>
</div>
@endsection