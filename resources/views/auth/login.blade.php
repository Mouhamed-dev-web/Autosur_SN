@extends('layouts.guest')
@section('title', 'Connexion')

@section('content')
<div class="mx-auto max-w-md rounded-lg bg-white p-6 shadow">
    <h1 class="text-2xl font-bold">Connexion</h1>

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium" for="identifier">Téléphone ou e-mail</label>
            <input id="identifier" name="identifier" value="{{ old('identifier') }}" required autofocus
                   class="mt-1 w-full rounded p-2 border border-gray-300">
            @error('identifier') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="password">Mot de passe</label>
            <input id="password" name="password" type="password" required
                   class="mt-1 w-full rounded p-2 border border-gray-300">
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" value="1"> Se souvenir de moi
        </label>

        <button class="w-full rounded bg-emerald-700 py-2 font-medium text-white">Se connecter</button>
    </form>

    <p class="mt-4 text-center text-sm">Pas encore de compte ? <a class="text-emerald-700 underline" href="{{ route('register') }}">S'inscrire</a></p>
</div>
@endsection