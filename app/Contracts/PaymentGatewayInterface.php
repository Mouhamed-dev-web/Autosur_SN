<?php

namespace App\Contracts;

use App\Models\Transaction;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Démarre le paiement chez l'opérateur.
     *
     * @return array{redirect_url: string, provider_ref: string|null}
     */
    public function initiate(Transaction $transaction): array;

    /** Vérifie que le webhook vient bien de l'opérateur (signature). */
    public function verifySignature(Request $request): bool;

    /**
     * Extrait l'événement du webhook, au format normalisé.
     *
     * @return array{event_id: string, reference: string, status: string, amount: int}
     */
    public function parseWebhook(Request $request): array;
}