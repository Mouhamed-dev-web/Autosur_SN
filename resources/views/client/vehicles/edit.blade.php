@extends('layouts.client')
@section('title', 'Modifier le véhicule')

@section('content')
<div class="mx-auto max-w-2xl rounded-lg bg-white p-6 shadow">
    <h1 class="text-2xl font-bold">Modifier {{ $vehicle->plate }}</h1>

    <form method="POST" action="{{ route('client.vehicles.update', $vehicle) }}" class="mt-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('client.vehicles._form')

        <div class="mt-6 flex gap-3">
            <button class="rounded bg-emerald-700 px-4 py-2 font-medium text-white">Mettre à jour</button>
            <a href="{{ route('client.vehicles.show', $vehicle) }}" class="rounded border px-4 py-2">Annuler</a>
        </div>
    </form>
</div>
@endsection