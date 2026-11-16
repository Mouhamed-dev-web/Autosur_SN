@extends('layouts.guest')
@section('title', 'Conditions d\'utilisation')

@section('content')
<div class="mx-auto my-8 max-w-3xl rounded-2xl bg-white/95 p-6 shadow-lg sm:p-10">
    <div class="mb-6 rounded bg-orange-50 p-3 text-sm text-orange-800">
        Version provisoire : à faire valider par un juriste avant la mise en ligne.
    </div>

    <h1 class="text-2xl font-bold text-gray-900">Conditions d'utilisation</h1>

    <h2 class="mt-6 font-semibold">1. Objet</h2>
    <p class="mt-1 text-sm text-gray-700">
        AutoSur.sn est une plateforme qui permet aux automobilistes de gérer leurs véhicules et documents, de suivre leurs échéances,
        de comparer des offres d'assurance, de prendre rendez-vous pour une visite technique et de demander un dépanneur.
    </p>

    <h2 class="mt-6 font-semibold">2. Compte</h2>
    <p class="mt-1 text-sm text-gray-700">
        L'inscription exige un numéro de téléphone que vous contrôlez, vérifié par un code SMS. Vous êtes responsable de la confidentialité
        de votre mot de passe et des informations que vous saisissez, qui doivent être exactes.
    </p>

    <h2 class="mt-6 font-semibold">3. Rôle d'AutoSur.sn</h2>
    <p class="mt-1 text-sm text-gray-700">
        AutoSur.sn met en relation les automobilistes et des partenaires indépendants (garages, dépanneurs, assureurs).
        Les prestations (visite technique, dépannage, contrat d'assurance) sont réalisées par ces partenaires.
        Le prix et les conditions d'une intervention de dépannage sont convenus entre le client et le dépanneur.
        Les primes d'assurance affichées sont indicatives : le contrat et le tarif définitifs relèvent de l'assureur.
    </p>

    <h2 class="mt-6 font-semibold">4. Frais et paiement</h2>
    <p class="mt-1 text-sm text-gray-700">
        L'inscription est gratuite. Des frais de service sont facturés pour la facilitation d'une visite technique ; leur montant est affiché
        avant toute demande. Les paiements en ligne sont traités par des opérateurs de paiement mobile ; AutoSur.sn ne conserve aucun code secret de portefeuille.
    </p>

    <h2 class="mt-6 font-semibold">5. Avis</h2>
    <p class="mt-1 text-sm text-gray-700">
        Seuls les clients ayant bénéficié d'une intervention terminée peuvent la noter. AutoSur.sn peut masquer un avis injurieux,
        diffamatoire ou sans rapport avec la prestation.
    </p>

    <h2 class="mt-6 font-semibold">6. Urgence</h2>
    <p class="mt-1 text-sm text-gray-700">
        Le service SOS Panne n'est pas un service de secours. En cas d'accident avec blessés ou de danger immédiat, contactez d'abord les secours.
    </p>

    <h2 class="mt-6 font-semibold">7. Responsabilité</h2>
    <p class="mt-1 text-sm text-gray-700">[À COMPLÉTER avec un juriste : limites de responsabilité d'AutoSur.sn, disponibilité du service.]</p>

    <h2 class="mt-6 font-semibold">8. Suspension</h2>
    <p class="mt-1 text-sm text-gray-700">
        AutoSur.sn peut suspendre un compte en cas d'usage frauduleux ou contraire aux présentes conditions.
    </p>

    <h2 class="mt-6 font-semibold">9. Droit applicable</h2>
    <p class="mt-1 text-sm text-gray-700">[À COMPLÉTER : droit applicable et juridiction compétente.]</p>

    <p class="mt-8 text-sm">
        <a class="text-emerald-700 underline" href="{{ route('legal.privacy') }}">Politique de confidentialité</a> ·
        <a class="text-emerald-700 underline" href="{{ route('contact') }}">Nous contacter</a>
    </p>
</div>
@endsection