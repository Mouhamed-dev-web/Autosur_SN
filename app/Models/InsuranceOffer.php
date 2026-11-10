<?php

namespace App\Models;

use App\Enums\CoverType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceOffer extends Model
{
    protected $fillable = [
        'company_name',
        'name',
        'cover_type',
        'base_premium',
        'per_cv_premium',
        'value_rate',
        'commission_rate',
        'description',
        'is_active',
        'partner_id',
    ];

    protected function casts(): array
    {
        return [
            'cover_type' => CoverType::class,
            'base_premium' => 'integer',
            'per_cv_premium' => 'integer',
            'value_rate' => 'float',
            'commission_rate' => 'float',
            'is_active' => 'boolean',
        ];
    }
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}