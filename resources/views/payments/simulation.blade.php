@extends('layouts.client')
@section('title', 'Paiement (simulation)')

@section('content')
<div class="mx-auto max-w-md rounded-lg bg-white p-6 shadow">
    <div class="rounded bg-orange-50 p-3 text-sm font-medium text-orange-800">
        SIMULATION : aucun argent réel n'est débité. Cette page remplace l'application {{ $transaction->provider->label() }}.
    </div>

    <h1 class="mt-4 text-xl font-bold">Payer avec {{ $transaction->provider->label() }}</h1>
    <p class="mt-2 text-sm text-gray-600">{{ $transaction->description() }}</p>
    <p class="mt-1 text-3xl font-bold text-emerald-700">{{ $transaction->amountForHumans() }}</p>
    <p class="mt-1 text-xs text-gray-500">Référence : {{ $transaction->reference }}</p>

    <form method="POST" action="{{ route('payments.simulation.complete', $transaction) }}" class="mt-6 flex flex-col gap-3">
        @csrf
        <button name="outcome" value="paid" class="rounded bg-emerald-700 px-4 py-3 font-medium text-white">Confirmer le paiement</button>
        <button name="outcome" value="failed" class="rounded border border-red-300 px-4 py-3 text-red-700">Simuler un échec</button>
    </form>
</div>
@endsection