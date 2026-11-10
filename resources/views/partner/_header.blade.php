@php
    $avg = $partner->averageRating();
    $count = $partner->visibleRatings()->count();
@endphp

<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold">{{ $partner->company_name }}</h1>
        <p class="text-sm text-gray-600">{{ $partner->type->label() }} · {{ $partner->contact_phone }}</p>
    </div>
    <div class="text-right text-sm">
        @if ($avg !== null)
            <p class="text-lg font-bold text-amber-600">★ {{ number_format($avg, 1, ',', '') }}</p>
            <p class="text-gray-500">{{ $count }} avis</p>
        @else
            <p class="text-gray-500">Pas encore d'avis</p>
        @endif
    </div>
</div>

@if (! $partner->is_validated)
    <div class="mt-4 rounded bg-orange-50 p-4 text-sm text-orange-800">
        Votre compte est en attente de validation par AutoSur.sn. Vous pourrez recevoir et traiter des demandes dès sa validation.
    </div>
@endif