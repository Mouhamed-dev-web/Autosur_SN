@extends('layouts.auth')
@section('title', 'Connexion')

@section('content')
<h1 class="text-2xl font-bold text-gray-900">Bon retour 👋</h1>
<p class="mt-1 text-sm text-gray-500">Connectez-vous à votre espace AutoSur.sn.</p>

<form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
    @csrf
    <x-auth.field name="identifier" label="Téléphone ou e-mail" icon="👤" required autofocus placeholder="77 123 45 67" />
    <x-auth.field name="password" label="Mot de passe" type="password" icon="🔒" required />

    <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2 text-gray-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-gray-300"> Se souvenir de moi
        </label>
        <a href="{{ route('password.request') }}" class="text-emerald-700 hover:underline">Mot de passe oublié ?</a>
    </div>

    <button class="w-full rounded-lg bg-emerald-700 py-3 font-semibold text-white shadow hover:bg-emerald-800">Se connecter</button>
</form>

<p class="mt-5 text-center text-sm text-gray-600">
    Pas encore de compte ? <a href="{{ route('register') }}" class="font-semibold text-emerald-700 hover:underline">S'inscrire</a>
</p>
<p class="mt-2 text-center text-sm text-gray-600">
    Vous êtes un professionnel ? <a href="{{ route('partners.register') }}" class="font-semibold text-emerald-700 hover:underline">Devenir partenaire</a>
</p>
@endsection