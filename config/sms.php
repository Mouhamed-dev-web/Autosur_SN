<?php

return [
    // « log » : écrit dans laravel.log (développement). « http » : vrai envoi.
    'driver' => env('SMS_DRIVER', 'log'),

    'http' => [
        'url' => env('SMS_URL'),
        'sender' => env('SMS_SENDER'),            // nom d'expéditeur, si le fournisseur l'accepte
        'token' => env('SMS_TOKEN'),
        'auth' => env('SMS_AUTH', 'bearer'),      // bearer | header | none
        'auth_header' => env('SMS_AUTH_HEADER', 'X-Api-Key'),
        'format' => env('SMS_FORMAT', 'json'),    // json | form
        'strip_plus' => (bool) env('SMS_STRIP_PLUS', false), // true : 221771234567 au lieu de +221771234567
        'timeout' => (int) env('SMS_TIMEOUT', 8),

        // Noms des champs attendus par le fournisseur (à adapter à sa documentation)
        'fields' => [
            'to' => env('SMS_FIELD_TO', 'to'),
            'message' => env('SMS_FIELD_MESSAGE', 'message'),
            'from' => env('SMS_FIELD_FROM', 'from'),
        ],
    ],
];