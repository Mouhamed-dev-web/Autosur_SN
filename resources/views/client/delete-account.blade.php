@extends('layouts.client')
@section('title', 'Supprimer mon compte')

@section('content')
@php
    $input = 'mt-1 w-full rounded border border-gray-300 p-2';
@endphp
<div class="mx-auto max-w-xl rounded-lg bg-white p-6 shadow">
    <h1 class="text-2xl font-bold text-red-700">Supprimer mon compte</h1>
    <p class="mt-2 text-sm text-gray-700">
        Cette action est <strong>définitive</strong> : vos véhicules, documents du coffre-fort, photos et alertes sont effacés.
        Les paiements déjà effectués sont conservés sous une forme anonyme, pour des raisons comptables.
    </p>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($blockers)
        <div class="mt-4 rounded bg-orange-50 p-3 text-sm text-orange-800">
            Suppression impossible pour le moment :
            <ul class="mt-1 list-disc pl-5">
                @foreach ($blockers as $reason)
                    <li>{{ $reason }}</li>
                @endforeach
            </ul>
            Terminez ou annulez ces opérations, puis revenez.
        </div>
    @else
        <form method="POST" action="{{ route('client.account.destroy') }}" class="mt-6 space-y-4">
            @csrf
            @method('DELETE')
            <div>
                <label class="block text-sm font-medium" for="current_password">Mot de passe actuel</label>
                <input id="current_password" name="current_password" type="password" required class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium" for="confirmation">Tapez SUPPRIMER pour confirmer</label>
                <input id="confirmation" name="confirmation" required autocomplete="off" class="{{ $input }}">
            </div>
            <button class="rounded bg-red-700 px-4 py-2 font-medium text-white">Supprimer définitivement mon compte</button>
        </form>
    @endif

    <p class="mt-6 text-sm"><a href="{{ route('client.security.edit') }}" class="text-emerald-700 underline">← Retour à la sécurité</a></p>
</div>
@endsection