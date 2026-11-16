@extends('layouts.guest')
@section('title', 'Mot de passe oublié')

@section('content')
<div class="mx-auto my-8 max-w-md rounded-2xl bg-white/95 p-6 shadow-lg">
    <h1 class="text-2xl font-bold">Mot de passe oublié</h1>
    <p class="mt-1 text-sm text-gray-600">Saisissez votre numéro de téléphone : nous vous envoyons un code par SMS.</p>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.send') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium" for="phone">Téléphone</label>
            <input id="phone" name="phone" type="tel" placeholder="77 123 45 67" value="{{ old('phone') }}" required autofocus
                   class="mt-1 w-full rounded border border-gray-300 p-2">
        </div>
        <button class="w-full rounded-xl bg-emerald-700 py-2.5 font-semibold text-white hover:bg-emerald-800">Recevoir un code</button>
    </form>

    <p class="mt-4 text-center text-sm"><a class="text-emerald-700 underline" href="{{ route('login') }}">Retour à la connexion</a></p>
</div>
@endsection