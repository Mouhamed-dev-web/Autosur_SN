<?php

return [
    // Passerelle utilisée : « simulation » en local. Wave / Orange Money seront ajoutées ici.
    'driver' => env('PAYMENT_DRIVER', 'simulation'),

    'gateways' => [
        'simulation' => App\Services\Payment\SimulatedGateway::class,
    ],

    'simulation' => [
        'secret' => env('PAYMENT_SIMULATION_SECRET'),
    ],
];