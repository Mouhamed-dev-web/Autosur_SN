@extends('layouts.auth')
@section('title', 'Vérification')

@section('content')
<h1 class="text-2xl font-bold text-gray-900">Vérifiez votre numéro</h1>
<p class="mt-1 text-sm text-gray-500">Saisissez le code à 6 chiffres envoyé au <strong>{{ $phone }}</strong>.</p>

<form method="POST" action="{{ route('otp.verify') }}" class="mt-6 space-y-4">
    @csrf
    <input name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" autofocus required
           class="w-full rounded-lg border border-gray-300 bg-gray-50 p-4 text-center text-3xl font-bold tracking-[.5em] focus:border-emerald-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-200">
    <button class="w-full rounded-lg bg-emerald-700 py-3 font-semibold text-white shadow hover:bg-emerald-800">Valider</button>
</form>

<form method="POST" action="{{ route('otp.resend') }}" class="mt-4 text-center">
    @csrf
    <button class="text-sm text-emerald-700 underline">Renvoyer le code</button>
</form>
@endsection