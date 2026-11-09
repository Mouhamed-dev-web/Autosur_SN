@php
    $unread = auth()->user()->unreadNotifications()->count();

    $links = [
        ['route' => 'client.dashboard',       'match' => 'client.dashboard',    'label' => 'Tableau de bord'],
        ['route' => 'client.vehicles.index',  'match' => 'client.vehicles.*',   'label' => 'Mes véhicules'],
        ['route' => 'client.documents.index', 'match' => 'client.documents.*',  'label' => 'Coffre-fort'],
        ['route' => 'client.insurance.index', 'match' => 'client.insurance.*',  'label' => 'Assurance'],
        ['route' => 'client.visits.index',    'match' => 'client.visits.*',     'label' => 'Visite technique'],
        ['route' => 'client.payments.index', 'match' => 'client.payments.*', 'label' => 'Paiements'],
        ['route' => 'client.alerts.index',    'match' => 'client.alerts.*',     'label' => 'Alertes', 'badge' => $unread],
        ['route' => 'client.profile.edit',    'match' => 'client.profile.*',    'label' => 'Mon profil'],
        ['route' => 'client.sos.index',       'match' => 'client.sos.*',        'label' => 'SOS Panne', 'danger' => true],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mon espace') | AutoSur.sn</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
            #main-menu ul { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .25rem 1rem; }
            #main-menu li a { padding: 0; }
            #main-menu form { margin: 0; padding: 0; border: 0; }
            #main-menu form button { width: auto; }
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
            #main-menu li a { display: block; padding: .75rem .5rem; }
            #main-menu form { margin-top: .75rem; padding-top: .75rem; border-top: 1px solid #e5e7eb; }
            #main-menu form button { width: 100%; }
        }
        /* Animation du background voiture */
        body {
            background-image: url('../assets/images/voiture.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            animation: animationVoiture 15s ease-in-out infinite alternate;
        }

        @keyframes animationVoiture {
            0% {
                background-size: 100%;
                background-position: center;
            }

            100% {
                background-size: 110%;
                background-position: center;
            }
        }
    </style>
</head>
<body class="text-gray-800 antialiased">
    @include('partials.animated-bg', ['variant' => 'soft'])
    <header class="relative bg-white shadow-sm">
        <nav class="mx-auto flex max-w-6xl items-center justify-between gap-3 p-4" aria-label="Navigation principale">
            <a href="{{ route('client.dashboard') }}" class="text-xl font-bold text-emerald-700">AutoSur.sn</a>

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
                        @php
                            $active = request()->routeIs($link['match']);
                            $danger = $link['danger'] ?? false;
                            $badge = $link['badge'] ?? 0;

                            if ($danger) {
                                $class = $active ? 'font-semibold text-red-700' : 'text-red-700 hover:underline';
                            } else {
                                $class = $active ? 'font-semibold text-emerald-700' : 'hover:text-emerald-700';
                            }
                        @endphp
                        <li>
                            <a href="{{ route($link['route']) }}" class="{{ $class }}"
                               @if ($active) aria-current="page" @endif>
                                {{ $link['label'] }}
                                @if ($badge > 0)
                                    <span class="ml-1 rounded-full bg-red-600 px-1.5 py-0.5 text-xs text-white">{{ $badge }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded border px-3 py-2 text-sm">Déconnexion</button>
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