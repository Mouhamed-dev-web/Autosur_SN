@extends('layouts.guest')
@section('title', 'Contact')

@section('content')
@php($input = 'mt-1 w-full rounded border border-gray-300 p-2')
<div class="mx-auto my-8 max-w-xl rounded-2xl bg-white/95 p-6 shadow-lg sm:p-8">
    <h1 class="text-2xl font-bold text-gray-900">Nous contacter</h1>
    <p class="mt-1 text-sm text-gray-600">Une question, un souci avec votre compte, ou l'envie de devenir partenaire ? Écrivez-nous.</p>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('contact.store') }}" class="mt-6 space-y-4">
        @csrf

        {{-- champ piège anti-spam : caché aux humains --}}
        <div style="position:absolute; left:-9999px;" aria-hidden="true">
            <label for="website">Ne pas remplir</label>
            <input id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div>
            <label class="block text-sm font-medium" for="name">Nom *</label>
            <input id="name" name="name" value="{{ old('name', auth()->user()?->name) }}" required class="{{ $input }}">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email', auth()->user()?->email) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="block text-sm font-medium" for="phone">Téléphone</label>
                <input id="phone" name="phone" type="tel" placeholder="77 123 45 67" value="{{ old('phone') }}" class="{{ $input }}">
            </div>
        </div>
        <p class="-mt-2 text-xs text-gray-500">Indiquez au moins l'un des deux pour que nous puissions vous répondre.</p>

        <div>
            <label class="block text-sm font-medium" for="subject">Sujet *</label>
            <select id="subject" name="subject" class="{{ $input }}">
                @foreach ($subjects as $key => $label)
                    <option value="{{ $key }}" @selected(old('subject', $selected) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium" for="message">Message *</label>
            <textarea id="message" name="message" rows="5" maxlength="2000" required class="{{ $input }}">{{ old('message') }}</textarea>
        </div>

        <button class="w-full rounded-xl bg-emerald-700 px-4 py-3 font-semibold text-white hover:bg-emerald-800">Envoyer</button>
    </form>
</div>
@endsection