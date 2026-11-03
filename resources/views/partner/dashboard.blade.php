{{-- resources/views/partner/dashboard.blade.php --}}
@extends('layouts.guest')
@section('title', 'Espace partenaire')

@section('content')
    <h1 class="text-2xl font-bold">Espace partenaire</h1>
    <p class="mt-2 text-gray-600">Bienvenue {{ auth()->user()->name }}.</p>
    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button class="rounded border px-3 py-1.5 text-sm">Se déconnecter</button>
    </form>
@endsection