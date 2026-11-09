<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, string $gateway, PaymentService $payments)
    {
        // La simulation n'existe jamais en production
        abort_if($gateway === 'simulation' && app()->isProduction(), 404);
        abort_unless(config("payments.gateways.{$gateway}"), 404);

        $result = $payments->handleWebhook($gateway, $request);

        return match ($result) {
            'invalid_signature' => response()->json(['error' => 'invalid signature'], 401),
            'unknown_reference' => response()->json(['error' => 'unknown reference'], 404),
            default => response()->json(['status' => $result]),
        };
    }
}