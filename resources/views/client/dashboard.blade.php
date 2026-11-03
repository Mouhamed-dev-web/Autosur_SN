{{-- resources/views/client/dashboard.blade.php --}}
@extends('layouts.guest')
@section('title', 'Mon espace')

@section('content')
    <h1 class="text-2xl font-bold">Bonjour {{ auth()->user()->name }}</h1>
    <p class="mt-2 text-gray-600">Votre espace automobiliste. Les modules arrivent à l'étape 3.</p>
    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button class="rounded border px-3 py-1.5 text-sm">Se déconnecter</button>
    </form>
@endsection