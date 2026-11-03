@extends('layouts.client')
@section('title', 'Mes alertes')

@section('content')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">Mes alertes</h1>
    @if ($user->unreadNotifications()->count() > 0)
        <form method="POST" action="{{ route('client.alerts.read') }}">
            @csrf
            <button class="text-sm text-emerald-700 underline">Tout marquer comme lu</button>
        </form>
    @endif
</div>

<div class="mt-6 rounded-lg bg-white p-5 shadow">
    <h2 class="font-semibold">Comment être prévenu ?</h2>
    <p class="mt-1 text-sm text-gray-500">Les alertes apparaissent toujours ici. Vous pouvez aussi les recevoir par :</p>

    <form method="POST" action="{{ route('client.alerts.preferences') }}" class="mt-3 space-y-2 text-sm">
        @csrf
        @method('PUT')
        <label class="flex items-center gap-2">
            <input type="checkbox" name="notify_mail" value="1" @checked($user->notify_mail)>
            E-mail @unless ($user->email) <span class="text-orange-700">(renseignez votre e-mail dans votre profil)</span> @endunless
        </label>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="notify_sms" value="1" @checked($user->notify_sms)>
            SMS au {{ $user->phone }}
        </label>
        <button class="rounded bg-emerald-700 px-3 py-1.5 font-medium text-white">Enregistrer</button>
    </form>
</div>

@forelse ($notifications as $n)
    <a href="{{ $n->data['url'] ?? '#' }}"
       class="mt-3 block rounded-lg p-4 shadow {{ $n->read_at ? 'bg-white' : 'border-l-4 border-orange-500 bg-orange-50' }}">
        <p class="font-medium">{{ $n->data['title'] ?? 'Alerte' }}</p>
        <p class="text-sm text-gray-700">{{ $n->data['message'] ?? '' }}</p>
        <p class="mt-1 text-xs text-gray-500">{{ $n->created_at->diffForHumans() }}</p>
    </a>
@empty
    <div class="mt-6 rounded-lg bg-white p-8 text-center shadow">
        <p class="text-gray-600">Aucune alerte pour l'instant.</p>
    </div>
@endforelse
@endsection