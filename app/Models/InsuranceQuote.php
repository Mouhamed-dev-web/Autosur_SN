<?php

namespace App\Models;

use App\Enums\CoverType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InsuranceQuote extends Model
{
    protected $fillable = [
        'vehicle_id',
        'cover_type',
        'fiscal_power',
        'vehicle_value',
        'duration_months',
    ];

    protected function casts(): array
    {
        return [
            'cover_type' => CoverType::class,
            'fiscal_power' => 'integer',
            'vehicle_value' => 'integer',
            'duration_months' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(InsuranceSubscription::class);
    }
}