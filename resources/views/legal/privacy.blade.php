@extends('layouts.guest')
@section('title', 'Confidentialité')

@section('content')
<div class="mx-auto my-8 max-w-3xl rounded-2xl bg-white/95 p-6 shadow-lg sm:p-10">
    <div class="mb-6 rounded bg-orange-50 p-3 text-sm text-orange-800">
        Version provisoire : à faire valider par un juriste (loi sénégalaise sur la protection des données personnelles) avant la mise en ligne.
    </div>

    <h1 class="text-2xl font-bold text-gray-900">Politique de confidentialité</h1>

    <h2 class="mt-6 font-semibold">Responsable du traitement</h2>
    <p class="mt-1 text-sm text-gray-700">
        AutoSur.sn. Contact : {{ config('autosur.contact_email') ?: '[À COMPLÉTER]' }}.
    </p>

    <h2 class="mt-6 font-semibold">Données collectées et finalités</h2>
    <ul class="mt-1 list-disc space-y-1 pl-5 text-sm text-gray-700">
        <li><strong>Compte</strong> : nom, téléphone, e-mail, adresse et photo (facultatives), mot de passe (conservé sous forme chiffrée irréversible). Pour créer et sécuriser votre compte.</li>
        <li><strong>Véhicules</strong> : immatriculation, marque, modèle, année, couleur, numéro de châssis, photo, échéances. Pour vous prévenir avant chaque échéance.</li>
        <li><strong>Coffre-fort</strong> : vos documents (PDF, images) sont stockés dans un espace privé, accessible à vous seul.</li>
        <li><strong>Position SOS</strong> : envoyée uniquement quand vous demandez un dépanneur, pour qu'il vous trouve.</li>
        <li><strong>Paiements</strong> : référence, montant, opérateur et statut. Nous ne recevons jamais vos codes secrets.</li>
        <li><strong>Messages de contact</strong> : nom, coordonnées, message, adresse IP (lutte contre le spam).</li>
    </ul>

    <h2 class="mt-6 font-semibold">Partage des données</h2>
    <ul class="mt-1 list-disc space-y-1 pl-5 text-sm text-gray-700">
        <li><strong>Assureur</strong> : votre nom, téléphone et les informations du véhicule concerné ne lui sont transmis qu'après votre consentement, lorsque vous choisissez son offre.</li>
        <li><strong>Dépanneur</strong> : il voit le problème et votre position à l'approche, et votre nom et téléphone uniquement après avoir accepté votre demande.</li>
        <li><strong>Garage</strong> : il voit votre nom et téléphone uniquement après avoir confirmé votre rendez-vous.</li>
        <li><strong>Prestataires techniques</strong> : hébergeur, passerelle SMS, opérateurs de paiement [À COMPLÉTER : noms].</li>
    </ul>

    <h2 class="mt-6 font-semibold">Durées de conservation</h2>
    <ul class="mt-1 list-disc space-y-1 pl-5 text-sm text-gray-700">
        <li>Position et lieu d'un SOS : {{ config('autosur.retention.sos_days') }} jours après la fin de la demande.</li>
        <li>Adresse IP des messages de contact : {{ config('autosur.retention.contact_ip_days') }} jours.</li>
        <li>Codes de vérification SMS : supprimés sous 24 heures.</li>
        <li>Compte, véhicules et documents : tant que votre compte existe. [À COMPLÉTER : durée après suppression, obligations comptables des transactions.]</li>
    </ul>

    <h2 class="mt-6 font-semibold">Sécurité</h2>
    <p class="mt-1 text-sm text-gray-700">
        Connexion chiffrée (HTTPS), mots de passe et codes SMS protégés, documents en stockage privé, double authentification pour les administrateurs.
    </p>

    <h2 class="mt-6 font-semibold">Vos droits</h2>
    <p class="mt-1 text-sm text-gray-700">
        Vous pouvez demander l'accès à vos données, leur rectification, leur suppression ou vous opposer à un traitement,
        en nous écrivant à {{ config('autosur.contact_email') ?: '[À COMPLÉTER]' }}.
        Vous pouvez aussi saisir l'autorité de protection des données personnelles du Sénégal [À COMPLÉTER : nom, coordonnées, à vérifier].
    </p>

    <p class="mt-8 text-sm">
        <a class="text-emerald-700 underline" href="{{ route('legal.terms') }}">Conditions d'utilisation</a> ·
        <a class="text-emerald-700 underline" href="{{ route('contact') }}">Nous contacter</a>
    </p>
</div>
@endsection