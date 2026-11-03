@extends('layouts.guest')
@section('title', 'Inscription')

@section('content')
<div class="mx-auto max-w-md rounded-lg bg-white p-6 shadow">
    <h1 class="text-2xl font-bold">Créer mon compte</h1>

    <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium" for="name">Nom complet</label>
            <input id="name" name="name" value="{{ old('name') }}" required
                   class="mt-1 w-full rounded border-gray-300 p-2 border">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="phone">Téléphone</label>
            <input id="phone" name="phone" type="tel" inputmode="tel" placeholder="77 123 45 67"
                   value="{{ old('phone') }}" required class="mt-1 w-full rounded p-2 border border-gray-300">
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="email">E-mail (facultatif)</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   class="mt-1 w-full rounded p-2 border border-gray-300">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="password">Mot de passe (8 caractères min.)</label>
            <input id="password" name="password" type="password" required
                   class="mt-1 w-full rounded p-2 border border-gray-300">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="password_confirmation">Confirmer le mot de passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   class="mt-1 w-full rounded p-2 border border-gray-300">
        </div>

        <button class="w-full rounded bg-emerald-700 py-2 font-medium text-white">Recevoir mon code SMS</button>
    </form>

    <p class="mt-4 text-center text-sm">Déjà inscrit ? <a class="text-emerald-700 underline" href="{{ route('login') }}">Connexion</a></p>
</div>
@endsection