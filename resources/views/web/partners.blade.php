@extends('layouts.guest')
@section('title', 'Partenaires')

@section('content')
<div class="mx-auto my-8 max-w-5xl rounded-2xl bg-white/95 p-6 shadow-lg sm:p-10">
    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Nos partenaires</h1>
    <p class="mt-2 text-gray-600">Garages, dépanneurs et assureurs référencés et validés par AutoSur.sn.</p>

    @php($any = $partners->flatten(1)->isNotEmpty())

    @if (! $any)
        <div class="mt-8 rounded-xl bg-gray-50 p-8 text-center">
            <p class="text-gray-700">Les premiers partenaires arrivent bientôt.</p>
            <a href="{{ route('contact', ['sujet' => 'partner']) }}" class="mt-4 inline-block rounded-xl bg-emerald-700 px-5 py-2.5 font-semibold text-white hover:bg-emerald-800">Devenir partenaire</a>
        </div>
    @else
        @foreach ($types as $type)
            @if (isset($partners[$type->value]))
                <h2 class="mt-8 text-lg font-semibold text-gray-900">{{ $type->label() }}s</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($partners[$type->value] as $partner)
                        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                            <p class="font-semibold text-gray-900">{{ $partner->company_name }}</p>
                            <p class="text-sm text-gray-600">{{ $type->label() }}</p>
                            <a href="tel:{{ $partner->contact_phone }}" class="mt-2 inline-block text-sm font-medium text-emerald-700 underline">{{ $partner->contact_phone }}</a>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach

        <div class="mt-10 text-center">
            <a href="{{ route('contact', ['sujet' => 'partner']) }}" class="inline-block rounded-xl border border-emerald-700 px-5 py-2.5 font-semibold text-emerald-700 hover:bg-emerald-50">Devenir partenaire</a>
        </div>
    @endif
</div>
@endsection