@extends('layouts.client')
@section('title', 'Visite technique')

@section('content')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">Visite technique</h1>
    <a href="{{ route('client.visits.create') }}" class="rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white">+ Prendre rendez-vous</a>
</div>

@error('visit')
    <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
@enderror
@error('payment')
    <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
@enderror

@forelse ($visits as $visit)
    <div class="mt-4 rounded-lg bg-white p-4 shadow">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <p class="font-semibold">{{ $visit->vehicle->plate }}
                    <span class="font-normal text-gray-600">· {{ $visit->vehicle->brand }} {{ $visit->vehicle->model }}</span>
                </p>
                <p class="text-sm text-gray-700">{{ $visit->scheduled_at->translatedFormat('l d/m/Y à H\hi') }}</p>
                <p class="mt-1 text-sm text-gray-500">
                    Frais de service : {{ $visit->feeForHumans() }}
                    · {{ $visit->fee_paid_at ? 'payés le '.$visit->fee_paid_at->format('d/m/Y') : 'à régler' }}
                </p>

                @if ($visit->canBePaid())
                    <form method="POST" action="{{ route('client.visits.pay', $visit) }}" class="mt-2 flex flex-wrap gap-2">
                        @csrf
                        @foreach (\App\Enums\PaymentProvider::cases() as $provider)
                            <button name="provider" value="{{ $provider->value }}"
                                    class="rounded border border-emerald-700 px-3 py-1.5 text-sm font-medium text-emerald-700">
                                Payer avec {{ $provider->label() }}
                            </button>
                        @endforeach
                    </form>
                @endif
            </div>

            <div class="text-right">
                <span class="rounded px-2 py-1 text-xs font-medium {{ $visit->status->color() }}">{{ $visit->status->label() }}</span>
                @if ($visit->canBeCancelled())
                    <form method="POST" action="{{ route('client.visits.cancel', $visit) }}" class="mt-2"
                          onsubmit="return confirm('Annuler ce rendez-vous ?')">
                        @csrf
                        <button class="text-sm text-red-700 underline">Annuler</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="mt-6 rounded-lg bg-white p-8 text-center shadow">
        <p class="text-gray-600">Aucun rendez-vous pour l'instant.</p>
        <a href="{{ route('client.visits.create') }}" class="mt-3 inline-block text-emerald-700 underline">Prendre mon premier rendez-vous</a>
    </div>
@endforelse
@endsection