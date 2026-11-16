<?php

return [
    'technical_visit' => [
        // Frais de service AutoSur.sn par visite, en FCFA (borné entre min et max)
        'fee' => (int) env('TECHNICAL_VISIT_FEE', 2000),
        'fee_min' => 1500,
        'fee_max' => 3000,

        // Durée de validité d'une visite technique, en mois (à confirmer avec la réglementation)
        'validity_months' => (int) env('TECHNICAL_VISIT_VALIDITY_MONTHS', 12),

        // Créneaux de rendez-vous
        'open_hour' => 8,
        'close_hour' => 17,
    ],

    'contact_email' => env('CONTACT_EMAIL'),

    // Durées de conservation des données personnelles
    'retention' => [
        'sos_days' => 30,      // position et lieu d'un SOS terminé ou annulé
        'contact_ip_days' => 90, // adresse IP des messages de contact
        'read_notifications_days' => 90,
    ],
];