<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AutoSur.sn') | AutoSur.sn</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .auth-wrap { min-height: 100vh; display: flex; flex-direction: column; background: #f3f4f6; }
        .auth-brand {
            position: relative; overflow: hidden; color: #fff;
            background: linear-gradient(160deg, #065f46 0%, #047857 55%, #0f172a 100%);
            padding: 1.25rem 1.25rem 0;
        }
        .auth-brand__text { position: relative; z-index: 2; }
        .auth-scene { position: relative; height: 110px; margin-top: .75rem; }
        .auth-form { flex: 1; display: flex; align-items: center; justify-content: center; padding: 1.25rem; }

        @media (min-width: 1024px) {
            .auth-wrap { flex-direction: row; }
            .auth-brand { width: 48%; padding: 3rem 3rem 0; display: flex; flex-direction: column; justify-content: space-between; }
            .auth-scene { height: 260px; margin-top: 0; }
            .auth-form { width: 52%; padding: 3rem; }
        }

        /* Route qui défile */
        .road { position: absolute; left: 0; right: 0; bottom: 0; height: 26%; background: #1f2937; }
        .road::before {
            content: ""; position: absolute; left: 0; right: 0; top: 48%; height: 4px;
            background: repeating-linear-gradient(90deg, #fbbf24 0 36px, transparent 36px 72px);
            animation: road-move 1.1s linear infinite;
        }
        @keyframes road-move { from { background-position: 0 0; } to { background-position: -72px 0; } }

        /* Voiture : léger rebond + arrivée */
        .car { position: absolute; bottom: 20%; left: 50%; width: min(300px, 72%); margin-left: calc(min(300px, 72%) / -2);
               animation: car-bounce .9s ease-in-out infinite alternate; }
        @keyframes car-bounce { from { transform: translateY(0); } to { transform: translateY(-3px); } }
        .wheel { transform-box: fill-box; transform-origin: center; animation: spin .6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Nuages */
        .cloud { position: absolute; background: rgba(255,255,255,.18); border-radius: 999px; height: 14px; animation: cloud 26s linear infinite; }
        .cloud::after { content: ""; position: absolute; left: 18%; top: -9px; width: 45%; height: 20px; background: inherit; border-radius: 999px; }
        .cloud--1 { top: 14%; width: 70px; left: -90px; }
        .cloud--2 { top: 34%; width: 52px; left: -90px; animation-duration: 38s; animation-delay: -12s; }
        @keyframes cloud { to { transform: translateX(120vw); } }

        .auth-card { width: 100%; max-width: 28rem; background: #fff; border-radius: 1.25rem;
                     box-shadow: 0 20px 45px rgba(15, 23, 42, .12); padding: 2rem 1.5rem; }
        @media (min-width: 640px) { .auth-card { padding: 2.25rem; } }
        .auth-card--wide { max-width: 36rem; }

        @media (prefers-reduced-motion: reduce) {
            .road::before, .car, .wheel, .cloud { animation: none; }
        }
    </style>
</head>
<body class="text-gray-800 antialiased">
<div class="auth-wrap">

    {{-- Panneau de marque --}}
    <aside class="auth-brand">
        <div class="auth-brand__text">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-2xl font-extrabold tracking-tight">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-lg">🚗</span>
                AutoSur<span class="text-emerald-300">.sn</span>
            </a>
            <p class="mt-3 hidden max-w-sm text-lg font-medium leading-snug text-emerald-50 lg:block">
                Votre auto, en toute sérénité : véhicules, documents, assurance, visite technique et dépannage au même endroit.
            </p>
            <ul class="mt-5 hidden space-y-2 text-sm text-emerald-100 lg:block">
                <li>✓ Alertes avant chaque échéance</li>
                <li>✓ Dépanneur géolocalisé en cas de panne</li>
                <li>✓ Paiement Wave et Orange Money</li>
            </ul>
        </div>

        <!-- {{-- Scène animée --}}
        <div class="auth-scene" aria-hidden="true">
            <span class="cloud cloud--1"></span>
            <span class="cloud cloud--2"></span>
            <div class="road"></div>

            <svg class="car" viewBox="0 0 300 120" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="150" cy="112" rx="120" ry="5" fill="#000" opacity=".25"/>
                {{-- carrosserie --}}
                <path d="M20 84 L28 62 Q32 54 42 52 L78 46 L100 24 Q106 18 116 18 L186 18 Q198 18 206 26 L232 50 L268 56 Q282 58 284 70 L286 84 Q286 92 278 92 L26 92 Q18 92 20 84 Z" fill="#fbbf24"/>
                {{-- vitres --}}
                <path d="M108 28 L120 28 L120 50 L88 50 Z" fill="#bae6fd"/>
                <path d="M128 28 L184 28 Q192 28 198 34 L216 50 L128 50 Z" fill="#bae6fd"/>
                <rect x="124" y="26" width="3" height="26" fill="#f59e0b"/>
                {{-- phares --}}
                <rect x="272" y="62" width="12" height="9" rx="3" fill="#fff7ed"/>
                <rect x="20" y="64" width="9" height="8" rx="2" fill="#ef4444"/>
                {{-- bande verte AutoSur --}}
                <rect x="40" y="70" width="228" height="6" rx="3" fill="#047857"/>
                {{-- roues --}}
                <g>
                    <circle cx="82" cy="94" r="20" fill="#111827"/>
                    <g class="wheel"><circle cx="82" cy="94" r="12" fill="#9ca3af"/>
                        <path d="M82 84v20M72 94h20" stroke="#4b5563" stroke-width="3"/></g>
                    <circle cx="224" cy="94" r="20" fill="#111827"/>
                    <g class="wheel"><circle cx="224" cy="94" r="12" fill="#9ca3af"/>
                        <path d="M224 84v20M214 94h20" stroke="#4b5563" stroke-width="3"/></g>
                </g>
            </svg>
        </div> -->
    </aside>

    {{-- Formulaire --}}
    <main class="auth-form">
        <div class="auth-card @yield('card_class')">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @yield('content')

            <p class="mt-6 text-center text-xs text-gray-400">
                <a href="{{ route('home') }}" class="underline">← Retour à l'accueil</a> ·
                <a href="{{ route('legal.privacy') }}" class="underline">Confidentialité</a>
            </p>
        </div>
    </main>
</div>
</body>
</html>