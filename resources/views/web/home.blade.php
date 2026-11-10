@extends('layouts.guest')
@section('title', 'Accueil')

@php
    $services = [
        ['icon' => '🚗', 'title' => 'Mes véhicules', 'text' => 'Enregistrez vos véhicules avec photo, immatriculation, châssis et suivez leurs échéances.'],
        ['icon' => '🗂️', 'title' => 'Coffre-fort numérique', 'text' => 'Carte grise, attestation, factures : vos documents PDF et photos en lieu sûr, avec dates d\'expiration.'],
        ['icon' => '🛡️', 'title' => 'Assurance', 'text' => 'Comparez les offres des assureurs partenaires et demandez votre devis en quelques clics.'],
        ['icon' => '🔧', 'title' => 'Visite technique', 'text' => 'Prenez rendez-vous, suivez le statut et réglez les frais de service par Wave ou Orange Money.'],
        ['icon' => '🚨', 'title' => 'SOS Panne', 'text' => 'Un dépanneur proche de vous, géolocalisé, qui accepte et vient vous chercher.'],
        ['icon' => '🔔', 'title' => 'Alertes', 'text' => 'Assurance, visite technique, documents : soyez prévenu avant chaque échéance, par SMS ou e-mail.'],
    ];

    $steps = [
        ['n' => '1', 'title' => 'Créez votre compte', 'text' => 'Avec votre numéro de téléphone, confirmé par un code SMS.'],
        ['n' => '2', 'title' => 'Ajoutez vos véhicules', 'text' => 'Renseignez vos informations et déposez vos documents.'],
        ['n' => '3', 'title' => 'Laissez-nous vous prévenir', 'text' => 'Rappels automatiques, assurance, rendez-vous et assistance en un seul endroit.'],
    ];
@endphp

@section('content')
<div class="home">

    {{-- HERO --}}
    <section class="home-card mx-auto mt-6 max-w-4xl p-8 text-center sm:mt-12 sm:p-12">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">La plateforme de l'automobiliste sénégalais</p>
        <h1 class="mt-3 text-3xl font-bold leading-tight text-gray-900 sm:text-5xl">
            Votre auto, en toute sérénité
        </h1>
        <p class="mx-auto mt-4 max-w-2xl text-base text-gray-600 sm:text-lg">
            Véhicules, documents, assurance, visite technique et assistance SOS :
            tout ce dont vous avez besoin, au même endroit.
        </p>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="rounded-xl bg-emerald-700 px-6 py-3 font-semibold text-white shadow hover:bg-emerald-800">Créer mon compte</a>
            <a href="{{ route('login') }}" class="rounded-xl border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-800 hover:bg-gray-50">Se connecter</a>
        </div>

        <p class="mt-5 text-sm text-gray-500">
            En panne ?
            <a href="{{ auth()->check() ? route('client.sos.create') : route('login') }}" class="font-semibold text-red-700 underline">Demander un dépanneur</a>
        </p>
    </section>

    {{-- SERVICES --}}
    <section class="mx-auto mt-10 max-w-6xl">
        <div class="home-card p-6 sm:p-10">
            <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Tout pour votre véhicule</h2>
            <p class="mx-auto mt-2 max-w-2xl text-center text-gray-600">Six services réunis dans un seul espace, simple à utiliser, même avec une connexion mobile moyenne.</p>

            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-2xl">{{ $service['icon'] }}</div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $service['title'] }}</h3>
                        <p class="mt-1 text-sm leading-6 text-gray-600">{{ $service['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- COMMENT ÇA MARCHE --}}
    <section class="mx-auto mt-10 max-w-6xl">
        <div class="home-card p-6 sm:p-10">
            <h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">Comment ça marche</h2>

            <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ($steps as $step)
                    <div class="text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-700 text-lg font-bold text-white">{{ $step['n'] }}</div>
                        <h3 class="mt-3 font-semibold text-gray-900">{{ $step['title'] }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $step['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- PROFESSIONNELS --}}
    <section class="mx-auto mt-10 max-w-6xl">
        <div class="home-card p-6 sm:p-10">
            <div class="grid items-center gap-6 md:grid-cols-3">
                <div class="md:col-span-2">
                    <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Vous êtes un professionnel ?</h2>
                    <p class="mt-2 text-gray-600">
                        Garages, dépanneurs et compagnies d'assurance : rejoignez AutoSur.sn pour recevoir de nouveaux clients
                        et gérer vos demandes depuis votre téléphone.
                    </p>
                </div>
                <div class="md:text-right">
                    <a href="{{ route('contact', ['sujet' => 'partner']) }}" class="inline-block rounded-xl border border-emerald-700 px-6 py-3 font-semibold text-emerald-700 hover:bg-emerald-50">Devenir partenaire</a>
                </div>
            </div>
        </div>
    </section>

    {{-- APPEL FINAL --}}
    <section class="mx-auto mb-6 mt-10 max-w-4xl">
        <div class="rounded-2xl bg-emerald-700/95 p-8 text-center text-white shadow-lg sm:p-12">
            <h2 class="text-2xl font-bold sm:text-3xl">Prêt à simplifier la vie de votre véhicule ?</h2>
            <p class="mx-auto mt-2 max-w-xl text-emerald-50">L'inscription est gratuite et prend moins de deux minutes.</p>
            <a href="{{ route('register') }}" class="mt-6 inline-block rounded-xl bg-white px-6 py-3 font-semibold text-emerald-800 hover:bg-emerald-50">Créer mon compte gratuitement</a>
        </div>
    </section>
</div>

<style>
    .home-card {
        background: rgba(255, 255, 255, .92);
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .15);
        backdrop-filter: blur(4px);
    }
</style>
@endsection