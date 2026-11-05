@extends('layouts.client')
@section('title', 'SOS Panne')

@section('content')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">SOS Panne</h1>
    <a href="{{ route('client.sos.create') }}" class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white">+ Demander un dépanneur</a>
</div>

@if (session('status'))
    {{-- le message flash est déjà affiché par le layout --}}
@endif

@error('sos')
    <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
@enderror

@forelse ($requests as $sos)
    <a href="{{ route('client.sos.show', $sos) }}" class="mt-4 block rounded-lg bg-white p-4 shadow hover:shadow-md">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <p class="font-semibold">{{ $sos->problem->label() }}</p>
                <p class="text-sm text-gray-600">
                    @if ($sos->vehicle) {{ $sos->vehicle->plate }} · @endif
                    {{ $sos->created_at->format('d/m/Y H\hi') }}
                    @if ($sos->partner) · {{ $sos->partner->company_name }} @endif
                </p>
            </div>
            <span class="rounded px-2 py-1 text-xs font-medium {{ $sos->status->color() }}">{{ $sos->status->label() }}</span>
        </div>
    </a>
@empty
    <div class="mt-6 rounded-lg bg-white p-8 text-center shadow">
        <p class="text-gray-600">Aucune demande SOS pour l'instant.</p>
    </div>
@endforelse
@endsection