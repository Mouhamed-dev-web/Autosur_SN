<?php

namespace App\Models;

use App\Enums\PartnerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    // Créé à l'inscription partenaire ; la validation est faite par l'administration
    protected $fillable = [
        'user_id',
        'type',
        'company_name',
        'contact_phone',
        'is_validated',
        'is_available',
        'base_lat',
        'base_lng',
        'radius_km',
    ];

    protected function casts(): array
    {
        return [
            'type' => PartnerType::class,
            'is_validated' => 'boolean',
            'is_available' => 'boolean',
            'base_lat' => 'float',
            'base_lng' => 'float',
            'radius_km' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sosRequests(): HasMany
    {
        return $this->hasMany(SosRequest::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(TechnicalVisit::class);
    }

    public function insuranceOffers(): HasMany
    {
        return $this->hasMany(InsuranceOffer::class);
    }

    /** Avis non masqués par la modération. */
    public function visibleRatings(): HasMany
    {
        return $this->hasMany(Rating::class)->where('is_hidden', false);
    }

    public function averageRating(): ?float
    {
        $avg = $this->visibleRatings()->avg('score');

        return $avg === null ? null : round((float) $avg, 1);
    }

    public function hasBaseLocation(): bool
    {
        return $this->base_lat !== null && $this->base_lng !== null;
    }
}