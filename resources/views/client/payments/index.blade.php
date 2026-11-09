@extends('layouts.client')
@section('title', 'Mes paiements')

@section('content')
<h1 class="text-2xl font-bold">Mes paiements</h1>

@forelse ($transactions as $tx)
    <a href="{{ route('client.payments.result', $tx) }}" class="mt-4 block rounded-lg bg-white p-4 shadow hover:shadow-md">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <p class="font-semibold">{{ $tx->description() }}</p>
                <p class="text-sm text-gray-600">
                    {{ $tx->provider->label() }} · {{ $tx->created_at->format('d/m/Y H\hi') }}
                </p>
                <p class="text-xs text-gray-500">Réf. {{ $tx->reference }}</p>
            </div>
            <div class="text-right">
                <p class="text-lg font-bold text-emerald-700">{{ $tx->amountForHumans() }}</p>
                <span class="rounded px-2 py-0.5 text-xs font-medium {{ $tx->status->color() }}">{{ $tx->status->label() }}</span>
            </div>
        </div>
    </a>
@empty
    <div class="mt-6 rounded-lg bg-white p-8 text-center shadow">
        <p class="text-gray-600">Aucun paiement pour l'instant.</p>
    </div>
@endforelse
@endsection