@extends('layouts.guest')
@section('title', 'Nouveau mot de passe')

@section('content')
@php
    $input = 'mt-1 w-full rounded border border-gray-300 p-2';
@endphp
<div class="mx-auto my-8 max-w-md rounded-2xl bg-white/95 p-6 shadow-lg">
    <h1 class="text-2xl font-bold">Nouveau mot de passe</h1>
    <p class="mt-1 text-sm text-gray-600">Saisissez le code reçu par SMS, puis choisissez un nouveau mot de passe.</p>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.reset') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium" for="code">Code à 6 chiffres</label>
            <input id="code" name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required autofocus
                   class="{{ $input }} text-center text-xl tracking-widest">
        </div>
        <div>
            <label class="block text-sm font-medium" for="password">Nouveau mot de passe (8 caractères min.)</label>
            <input id="password" name="password" type="password" required class="{{ $input }}">
        </div>
        <div>
            <label class="block text-sm font-medium" for="password_confirmation">Confirmer</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="{{ $input }}">
        </div>
        <button class="w-full rounded-xl bg-emerald-700 py-2.5 font-semibold text-white hover:bg-emerald-800">Changer le mot de passe</button>
    </form>

    <p class="mt-4 text-center text-sm"><a class="text-emerald-700 underline" href="{{ route('password.request') }}">Renvoyer un code</a></p>
</div>
@endsection