@extends('layouts.guest')
@section('title', 'Vérification')

@section('content')
<div class="mx-auto max-w-md rounded-lg bg-white p-6 shadow">
    <h1 class="text-2xl font-bold">Vérifiez votre numéro</h1>
    <p class="mt-2 text-sm text-gray-600">Saisissez le code à 6 chiffres envoyé au {{ $phone }}.</p>

    <form method="POST" action="{{ route('otp.verify') }}" class="mt-6 space-y-4">
        @csrf
        <input name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" autofocus required
               class="w-full rounded border border-gray-300 p-3 text-center text-2xl tracking-widest">
        @error('code') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

        <button class="w-full rounded bg-emerald-700 py-2 font-medium text-white">Valider</button>
    </form>

    <form method="POST" action="{{ route('otp.resend') }}" class="mt-4 text-center">
        @csrf
        <button class="text-sm text-emerald-700 underline">Renvoyer le code</button>
    </form>
</div>
@endsection