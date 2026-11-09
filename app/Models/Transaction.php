<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    // Créé uniquement par PaymentService, jamais à partir d'une saisie utilisateur
    protected $fillable = [
        'user_id',
        'provider',
        'gateway',
        'reference',
        'provider_ref',
        'amount',
        'currency',
        'status',
        'event_id',
        'payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'status' => PaymentStatus::class,
            'amount' => 'integer',
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /** Les URLs utilisent la référence plutôt que l'id. */
    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function amountForHumans(): string
    {
        return number_format($this->amount, 0, ',', ' ').' FCFA';
    }

    public function description(): string
    {
        $payable = $this->payable;

        return $payable instanceof TechnicalVisit
            ? 'Visite technique · '.($payable->vehicle?->plate ?? '')
            : 'Paiement';
    }
}