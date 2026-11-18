@extends('layouts.auth')
@section('title', 'Nouveau mot de passe')

@section('content')
<h1 class="text-2xl font-bold text-gray-900">Nouveau mot de passe</h1>
<p class="mt-1 text-sm text-gray-500">Saisissez le code reçu par SMS, puis choisissez un nouveau mot de passe.</p>

<form method="POST" action="{{ route('password.reset') }}" class="mt-6 space-y-4">
    @csrf
    <input name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required autofocus placeholder="Code à 6 chiffres"
           class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-center text-2xl font-bold tracking-[.4em] focus:border-emerald-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200">
    <x-auth.field name="password" label="Nouveau mot de passe" type="password" icon="🔒" required hint="8 caractères minimum" />
    <x-auth.field name="password_confirmation" label="Confirmer" type="password" icon="🔒" required />
    <button class="w-full rounded-lg bg-emerald-700 py-3 font-semibold text-white shadow hover:bg-emerald-800">Changer le mot de passe</button>
</form>

<p class="mt-5 text-center text-sm"><a class="text-emerald-700 hover:underline" href="{{ route('password.request') }}">Renvoyer un code</a></p>
@endsection