@extends('layouts.partner')
@section('title', 'Tableau de bord')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <p class="text-sm font-semibold text-emerald-700">{{ strtoupper($partner->type->label()) }}</p>
        <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $partner->company_name }}</h1>
        <p class="text-sm text-gray-500">{{ $partner->contact_phone }}</p>
    </div>
    <div class="text-right text-sm">
        @if ($rating !== null)
            <p class="text-xl font-bold text-amber-600">★ {{ number_format($rating, 1, ',', '') }}</p>
            <p class="text-gray-500">{{ $ratingCount }} avis</p>
        @else
            <p class="text-gray-500">Pas encore d'avis</p>
        @endif
    </div>
</div>

@if (! $partner->is_validated)
    <div class="mt-4 rounded-lg bg-orange-50 p-4 text-sm text-orange-800">
        Votre compte est en attente de validation par AutoSur.sn. Vous pourrez recevoir et traiter des demandes dès sa validation.
    </div>
@endif

@if ($partner->type !== \App\Enums\PartnerType::ASSURANCE && ! $partner->hasBaseLocation())
    <div class="mt-4 rounded-lg bg-blue-50 p-4 text-sm text-blue-800">
        Votre position n'est pas renseignée.
        <a href="{{ route('partner.profile.edit') }}" class="font-semibold underline">Localiser mon entreprise</a>
    </div>
@endif

<div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @foreach ($cards as $card)
        <div class="flex items-center justify-between rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="min-w-0">
                <p class="text-xs text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-1 truncate text-xl font-bold text-gray-900">{{ $card['value'] }}</p>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-xl {{ $card['tone'] }}">{{ $card['icon'] }}</div>
        </div>
    @endforeach
</div>

<section class="mt-6 rounded-2xl bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-gray-900">{{ $listTitle }}</h2>
        <a href="{{ $listUrl }}" class="text-sm text-emerald-700 underline">Voir tout</a>
    </div>

    @forelse ($rows as $row)
        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-100 p-3">
            <div>
                <p class="text-sm font-medium text-gray-900">{{ $row['title'] }}</p>
                <p class="text-xs text-gray-500">{{ $row['sub'] }}</p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $row['badgeClass'] }}">{{ $row['badge'] }}</span>
        </div>
    @empty
        <p class="mt-3 text-sm text-gray-500">{{ $emptyText }}</p>
    @endforelse

    <a href="{{ $primary['url'] }}" class="mt-5 inline-block rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">{{ $primary['label'] }}</a>
</section>
@endsection