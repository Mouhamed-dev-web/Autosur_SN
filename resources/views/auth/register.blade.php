@extends('layouts.auth')
@section('title', 'Inscription')

@section('content')
<h1 class="text-2xl font-bold text-gray-900">Créer mon compte</h1>
<p class="mt-1 text-sm text-gray-500">Gratuit, en moins de deux minutes. Un code SMS confirme votre numéro.</p>

<form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
    @csrf
    <x-auth.field name="name" label="Nom complet" icon="👤" required />
    <x-auth.field name="phone" label="Téléphone" type="tel" icon="📱" required placeholder="77 123 45 67" inputmode="tel" />
    <x-auth.field name="email" label="E-mail (facultatif)" type="email" icon="✉️" />

    <div class="grid gap-4 sm:grid-cols-2">
        <x-auth.field name="password" label="Mot de passe" type="password" icon="🔒" required hint="8 caractères minimum" />
        <x-auth.field name="password_confirmation" label="Confirmer" type="password" icon="🔒" required />
    </div>

    <button class="w-full rounded-lg bg-emerald-700 py-3 font-semibold text-white shadow hover:bg-emerald-800">Recevoir mon code SMS</button>

    <p class="text-center text-xs text-gray-500">
        En créant un compte, vous acceptez les
        <a class="underline" href="{{ route('legal.terms') }}">conditions d'utilisation</a> et la
        <a class="underline" href="{{ route('legal.privacy') }}">politique de confidentialité</a>.
    </p>
</form>

<p class="mt-5 text-center text-sm text-gray-600">
    Déjà inscrit ? <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:underline">Connexion</a>
</p>
@endsection