@include('partials.animated-bg', ['variant' => 'hero'])
@php
    $links = [
        ['href' => route('home'), 'label' => 'Accueil', 'active' => request()->routeIs('home')],
        ['href' => route('partners'), 'label' => 'Partenaires', 'active' => request()->routeIs('partners')],
        ['href' => route('contact'), 'label' => 'Contact', 'active' => request()->routeIs('contact')],
    ];

    $user = auth()->user();
    $space = null;

    if ($user) {
        $space = match (true) {
            $user->hasRole('admin') => url('/admin'),
            $user->hasAnyRole(['garage', 'depanneur', 'assurance']) => route('partner.dashboard'),
            default => route('client.dashboard'),
        };
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AutoSur.sn') | Votre auto, en toute sérénité</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* Téléphone : menu caché, ouvert par le bouton */
        #main-menu { display: none; }
        #main-menu.is-open { display: block; }
        #menu-toggle { display: inline-flex; }

        /* Ordinateur et tablette (768 px et plus) : menu toujours visible, sans bouton */
        @media (min-width: 768px) {
            #menu-toggle { display: none; }
            #main-menu {
                display: flex !important;
                flex: 1;
                align-items: center;
                justify-content: flex-end;
                gap: 1rem;
                position: static;
                padding: 0;
                border: 0;
                box-shadow: none;
            }
            #main-menu ul { display: flex; align-items: center; gap: 1rem; }
            #main-menu li a.nav-link { padding: 0; }
            #main-menu .nav-actions { display: flex; align-items: center; gap: .75rem; margin: 0; padding: 0; border: 0; }
        }

        /* Téléphone : panneau sous l'en-tête */
        @media (max-width: 767px) {
            #main-menu {
                position: absolute;
                left: 0; right: 0; top: 100%;
                z-index: 20;
                background: #fff;
                border-top: 1px solid #e5e7eb;
                padding: 1rem;
                box-shadow: 0 6px 12px rgba(0, 0, 0, .1);
            }
            #main-menu ul { display: flex; flex-direction: column; gap: .25rem; }
            #main-menu li a.nav-link { display: block; padding: .75rem .5rem; }
            #main-menu .nav-actions {
                display: flex; flex-direction: column; gap: .5rem;
                margin-top: .75rem; padding-top: .75rem; border-top: 1px solid #e5e7eb;
            }
            #main-menu .nav-actions a,
            #main-menu .nav-actions button { display: block; width: 100%; text-align: center; padding: .6rem .75rem; }
        }
    </style>
</head>
<body class="text-gray-800 antialiased">
    <header class="relative bg-white/95 shadow-sm backdrop-blur">
        <nav class="mx-auto flex max-w-6xl items-center justify-between gap-3 p-4" aria-label="Navigation principale">
            <a href="{{ route('home') }}" class="text-xl font-bold text-emerald-700">AutoSur.sn</a>

            <button type="button" id="menu-toggle"
                    class="h-10 w-10 items-center justify-center rounded border border-gray-300"
                    aria-controls="main-menu" aria-expanded="false" aria-label="Ouvrir le menu">
                <svg id="icon-open" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg id="icon-close" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="display:none">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>

            <div id="main-menu">
                <ul class="text-sm">
                    @foreach ($links as $link)
                        <li>
                            <a href="{{ $link['href'] }}"
                               class="nav-link {{ $link['active'] ? 'font-semibold text-emerald-700' : 'hover:text-emerald-700' }}"
                               @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="nav-actions text-sm">
                    @auth
                        <a href="{{ $space }}" class="rounded bg-emerald-700 px-3 py-1.5 text-white">Mon espace</a>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0">
                            @csrf
                            <button class="rounded border px-3 py-1.5">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded border px-3 py-1.5 hover:text-emerald-700">Connexion</a>
                        <a href="{{ route('register') }}" class="rounded bg-emerald-700 px-3 py-1.5 text-white">S'inscrire</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl p-4">
        @if (session('status'))
            <div class="mb-4 rounded bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="mt-12 border-t border-white/20 p-6 text-center text-sm text-white/80">
        © {{ date('Y') }} AutoSur.sn, Dakar, Sénégal
    </footer>

    @livewireScripts

    <script>
        (function () {
            var toggle = document.getElementById('menu-toggle');
            var menu = document.getElementById('main-menu');
            var iconOpen = document.getElementById('icon-open');
            var iconClose = document.getElementById('icon-close');
            if (!toggle || !menu) { return; }

            function setOpen(open) {
                menu.classList.toggle('is-open', open);
                iconOpen.style.display = open ? 'none' : '';
                iconClose.style.display = open ? '' : 'none';
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
            }

            toggle.addEventListener('click', function () {
                setOpen(!menu.classList.contains('is-open'));
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { setOpen(false); }
            });

            menu.querySelectorAll('a').forEach(function (a) {
                a.addEventListener('click', function () { setOpen(false); });
            });
        })();
    </script>
</body>
</html>