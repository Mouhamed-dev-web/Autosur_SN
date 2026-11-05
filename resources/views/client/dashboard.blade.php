@extends('layouts.client')
@section('title', 'Tableau de bord')

@section('content')
    <h1 class="text-2xl font-bold">Bonjour {{ auth()->user()->name }}</h1>
    <a href="{{ route('client.sos.create') }}" class="mt-4 block rounded-lg bg-red-700 p-4 text-center text-lg font-bold text-white shadow hover:bg-red-800">
        🚨 En panne ? Demander un dépanneur
    </a>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <a href="{{ route('client.vehicles.index') }}" class="rounded-lg bg-white p-5 shadow hover:shadow-md">
            <p class="text-sm text-gray-500">Mes véhicules</p>
            <p class="mt-1 text-3xl font-bold text-emerald-700">{{ $vehicles->count() }}</p>
        </a>
        <a href="{{ route('client.vehicles.create') }}" class="flex items-center justify-center rounded-lg border-2 border-dashed border-emerald-600 p-5 font-medium text-emerald-700 hover:bg-emerald-50">
            + Ajouter un véhicule
        </a>
    </div>

    @php($soon = $vehicles->filter->technicalVisitDueSoon())
    @if ($soon->isNotEmpty())
        <div class="mt-6 rounded bg-orange-50 p-4 text-sm text-orange-800">
            Visite technique à prévoir (moins de 30 jours ou dépassée) :
            @foreach ($soon as $v) <strong>{{ $v->plate }}</strong>@if (! $loop->last), @endif @endforeach
        </div>
    @endif
@endsection