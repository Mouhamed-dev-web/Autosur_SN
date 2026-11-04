<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceSubscription extends Model
{
    // Créé uniquement par InsuranceService, jamais à partir d'une saisie utilisateur
    protected $fillable = [
        'user_id',
        'insurance_quote_id',
        'insurance_offer_id',
        'status',
        'premium_amount',
        'commission_rate',
        'commission_amount',
        'consented_at',
        'starts_at',
        'ends_at',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'premium_amount' => 'integer',
            'commission_rate' => 'float',
            'commission_amount' => 'integer',
            'consented_at' => 'datetime',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'activated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(InsuranceQuote::class, 'insurance_quote_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(InsuranceOffer::class, 'insurance_offer_id');
    }
}