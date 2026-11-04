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
];