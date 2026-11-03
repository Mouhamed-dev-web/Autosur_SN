@extends('layouts.client')
@section('title', 'Mon profil')

@section('content')
<div class="mx-auto max-w-xl rounded-lg bg-white p-6 shadow">
    <h1 class="text-2xl font-bold">Mon profil</h1>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('client.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-4">
            @if ($user->photoUrl())
                <img src="{{ $user->photoUrl() }}" alt="Photo de profil" class="h-16 w-16 rounded-full object-cover">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-xl font-bold text-emerald-700">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <label class="block text-sm font-medium" for="photo">Photo (facultative)</label>
                <input id="photo" name="photo" type="file" accept="image/png,image/jpeg" class="mt-1 text-sm">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="first_name">Prénom *</label>
                <input id="first_name" name="first_name" value="{{ old('first_name', $firstName) }}" required class="mt-1 w-full rounded border border-gray-300 p-2">
            </div>
            <div>
                <label class="block text-sm font-medium" for="last_name">Nom *</label>
                <input id="last_name" name="last_name" value="{{ old('last_name', $lastName) }}" required class="mt-1 w-full rounded border border-gray-300 p-2">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">Téléphone</label>
            <input value="{{ $user->phone }}" disabled class="mt-1 w-full rounded border border-gray-200 bg-gray-100 p-2">
        </div>

        <div>
            <label class="block text-sm font-medium" for="email">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="mt-1 w-full rounded border border-gray-300 p-2">
        </div>

        <div>
            <label class="block text-sm font-medium" for="address">Adresse</label>
            <input id="address" name="address" value="{{ old('address', $user->address) }}" class="mt-1 w-full rounded border border-gray-300 p-2">
        </div>

        <button class="rounded bg-emerald-700 px-4 py-2 font-medium text-white">Enregistrer</button>
    </form>
</div>
@endsection