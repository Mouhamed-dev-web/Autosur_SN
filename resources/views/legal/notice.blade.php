@extends('layouts.guest')
@section('title', 'Mentions légales')

@section('content')
<div class="mx-auto my-8 max-w-3xl rounded-2xl bg-white/95 p-6 shadow-lg sm:p-10">
    <div class="mb-6 rounded bg-orange-50 p-3 text-sm text-orange-800">
        Version provisoire : les champs <strong>[À COMPLÉTER]</strong> doivent être renseignés et le texte validé par un juriste avant la mise en ligne.
    </div>

    <h1 class="text-2xl font-bold text-gray-900">Mentions légales</h1>

    <h2 class="mt-6 font-semibold">Éditeur du site</h2>
    <p class="mt-1 text-sm text-gray-700">
        AutoSur.sn, projet porté par Babacar DIOUME, Dakar, Sénégal.<br>
        Forme juridique : [À COMPLÉTER]<br>
        NINEA / RCCM : [À COMPLÉTER]<br>
        Adresse : [À COMPLÉTER]<br>
        Téléphone : [À COMPLÉTER]<br>
        E-mail : {{ config('autosur.contact_email') ?: '[À COMPLÉTER]' }}
    </p>

    <h2 class="mt-6 font-semibold">Directeur de la publication</h2>
    <p class="mt-1 text-sm text-gray-700">[À COMPLÉTER]</p>

    <h2 class="mt-6 font-semibold">Hébergement</h2>
    <p class="mt-1 text-sm text-gray-700">Hébergeur : [À COMPLÉTER] (nom, adresse, pays).</p>

    <h2 class="mt-6 font-semibold">Propriété intellectuelle</h2>
    <p class="mt-1 text-sm text-gray-700">
        Les contenus du site (textes, logo, interface) appartiennent à l'éditeur, sauf mention contraire.
        Les cartes sont fournies par © OpenStreetMap contributors.
    </p>

    <p class="mt-8 text-sm"><a class="text-emerald-700 underline" href="{{ route('contact') }}">Nous contacter</a></p>
</div>
@endsection