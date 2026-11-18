@extends('layouts.auth')
@section('title', 'Mot de passe oublié')

@section('content')
<h1 class="text-2xl font-bold text-gray-900">Mot de passe oublié</h1>
<p class="mt-1 text-sm text-gray-500">Saisissez votre numéro : nous vous envoyons un code par SMS.</p>

<form method="POST" action="{{ route('password.send') }}" class="mt-6 space-y-4">
    @csrf
    <x-auth.field name="phone" label="Téléphone" type="tel" icon="📱" required autofocus placeholder="77 123 45 67" />
    <button class="w-full rounded-lg bg-emerald-700 py-3 font-semibold text-white shadow hover:bg-emerald-800">Recevoir un code</button>
</form>

<p class="mt-5 text-center text-sm"><a class="text-emerald-700 hover:underline" href="{{ route('login') }}">← Retour à la connexion</a></p>
@endsection