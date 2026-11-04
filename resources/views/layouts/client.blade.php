<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mon espace') | AutoSur.sn</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white shadow-sm">
        <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 p-4">
            <a href="{{ route('client.dashboard') }}" class="text-xl font-bold text-emerald-700">AutoSur.sn</a>
            @php($unread = auth()->user()->unreadNotifications()->count())

            <div class="flex flex-wrap items-center gap-4 text-sm">
                <a href="{{ route('client.dashboard') }}"
                   class="{{ request()->routeIs('client.dashboard') ? 'font-semibold text-emerald-700' : 'hover:text-emerald-700' }}">Tableau de bord</a>
                <a href="{{ route('client.vehicles.index') }}"
                   class="{{ request()->routeIs('client.vehicles.*') ? 'font-semibold text-emerald-700' : 'hover:text-emerald-700' }}">Mes véhicules</a>
                <a href="{{ route('client.profile.edit') }}"
                   class="{{ request()->routeIs('client.profile.*') ? 'font-semibold text-emerald-700' : 'hover:text-emerald-700' }}">Mon profil</a>
                   <a href="{{ route('client.alerts.index') }}"
            class="{{ request()->routeIs('client.alerts.*') ? 'font-semibold text-emerald-700' : 'hover:text-emerald-700' }}">
                Alertes
                @if ($unread > 0)
                    <span class="ml-1 rounded-full bg-red-600 px-1.5 py-0.5 text-xs text-white">{{ $unread }}</span>
                @endif
            </a>
                   <a href="{{ route('client.insurance.index') }}"
            class="{{ request()->routeIs('client.insurance.*') ? 'font-semibold text-emerald-700' : 'hover:text-emerald-700' }}">Assurance</a>
            <a href="{{ route('client.visits.index') }}"
            class="{{ request()->routeIs('client.visits.*') ? 'font-semibold text-emerald-700' : 'hover:text-emerald-700' }}">Visite technique</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded border px-3 py-1.5">Déconnexion</button>
                </form>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl p-4">
        @if (session('status'))
            <div class="mb-4 rounded bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>