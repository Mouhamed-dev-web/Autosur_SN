<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AutoSur.sn') | Votre auto, en toute sérénité</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white shadow-sm">
        <nav class="mx-auto flex max-w-6xl items-center justify-between p-4">
            <a href="{{ route('home') }}" class="text-xl font-bold text-emerald-700">AutoSur.sn</a>
            <div class="flex gap-4 text-sm">
                <a href="#" class="hover:text-emerald-700">Partenaires</a>
                <a href="#" class="hover:text-emerald-700">Contact</a>
                <a href="#" class="rounded bg-emerald-700 px-3 py-1.5 text-white">Connexion</a>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl p-4">
        <a href="{{ route('login') }}" class="hover:text-emerald-700">Connexion</a>
        <a href="{{ route('register') }}" class="rounded bg-emerald-700 px-3 py-1.5 text-white">S'inscrire</a>
        @if (session('status'))
            <div class="mb-4 rounded bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="mt-12 border-t p-6 text-center text-sm text-gray-500">
        © {{ date('Y') }} AutoSur.sn, Dakar, Sénégal
    </footer>
    @livewireScripts
</body>
</html>