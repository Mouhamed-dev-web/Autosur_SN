<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Espace partenaire') | AutoSur.sn</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white shadow-sm">
        <nav class="mx-auto flex max-w-4xl items-center justify-between p-4">
            <a href="{{ route('partner.dashboard') }}" class="text-xl font-bold text-emerald-700">AutoSur.sn <span class="text-sm font-normal text-gray-500">Partenaire</span></a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded border px-3 py-1.5 text-sm">Déconnexion</button>
            </form>
        </nav>
    </header>

    <main class="mx-auto max-w-4xl p-4">
        @if (session('status'))
            <div class="mb-4 rounded bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->has('sos'))
            <div class="mb-4 rounded bg-red-50 p-3 text-sm text-red-700">{{ $errors->first('sos') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>