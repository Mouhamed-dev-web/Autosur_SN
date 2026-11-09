@extends('layouts.client')
@section('title', 'Paiement')

@section('content')
@php
    $pending = $transaction->status === \App\Enums\PaymentStatus::PENDING;
@endphp
<div class="mx-auto max-w-md rounded-lg bg-white p-6 shadow">
    <div class="flex items-start justify-between gap-2">
        <h1 class="text-xl font-bold">Paiement</h1>
        <span class="rounded px-2 py-1 text-sm font-medium {{ $transaction->status->color() }}">{{ $transaction->status->label() }}</span>
    </div>

    <p class="mt-3 text-sm text-gray-600">{{ $transaction->description() }}</p>
    <p class="text-2xl font-bold text-emerald-700">{{ $transaction->amountForHumans() }}</p>
    <p class="mt-1 text-xs text-gray-500">{{ $transaction->provider->label() }} · Référence {{ $transaction->reference }}</p>

    @if ($pending)
        <div class="mt-4 rounded bg-orange-50 p-3 text-sm text-orange-800">
            En attente de la confirmation de l'opérateur. Cette page se met à jour toute seule.
        </div>
    @elseif ($transaction->status === \App\Enums\PaymentStatus::PAID)
        <div class="mt-4 rounded bg-emerald-50 p-3 text-sm text-emerald-800">
            Paiement confirmé{{ $transaction->paid_at ? ' le '.$transaction->paid_at->format('d/m/Y à H\hi') : '' }}. Merci !
        </div>
    @elseif ($transaction->status === \App\Enums\PaymentStatus::FAILED)
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            Le paiement a échoué. Aucun montant n'a été débité. Vous pouvez réessayer depuis vos visites techniques.
        </div>
    @endif

    <div class="mt-6 flex gap-3 text-sm">
        <a href="{{ route('client.visits.index') }}" class="rounded bg-emerald-700 px-4 py-2 font-medium text-white">Mes visites</a>
        <a href="{{ route('client.payments.index') }}" class="rounded border px-4 py-2">Mes paiements</a>
    </div>
</div>

@if ($pending)
    <script>
        setTimeout(function () { location.reload(); }, 5000);
    </script>
@endif
@endsection