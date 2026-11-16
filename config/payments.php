<?php

return [
    // Interrupteur global : false tant qu'aucun vrai opérateur n'est branché en production
    'enabled' => env('PAYMENTS_ENABLED', true),

    // Passerelle utilisée : « simulation » en local. Wave / Orange Money seront ajoutées ici.
    'driver' => env('PAYMENT_DRIVER', 'simulation'),

    'gateways' => [
        'simulation' => App\Services\Payment\SimulatedGateway::class,
    ],

    'simulation' => [
        'secret' => env('PAYMENT_SIMULATION_SECRET'),
    ],
];