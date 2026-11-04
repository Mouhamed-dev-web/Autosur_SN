<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use App\Enums\InsuranceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'plate',
        'brand',
        'model',
        'year',
        'color',
        'vin',
        'insurance_status',
        'insurance_expires_at',
        'technical_visit_last_at',
        'technical_visit_expires_at',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'insurance_status' => InsuranceStatus::class,
            'insurance_expires_at' => 'date',
            'technical_visit_last_at' => 'date',
            'technical_visit_expires_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Supprimer le véhicule supprime aussi sa photo
        static::deleting(function (Vehicle $vehicle) {
            if ($vehicle->photo_path) {
                Storage::disk('public')->delete($vehicle->photo_path);
            }
        });
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function technicalVisitDueSoon(): bool
    {
        return $this->technical_visit_expires_at !== null
            && $this->technical_visit_expires_at->lte(now()->addDays(30));
    }
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
    public function technicalVisits(): HasMany
    {
        return $this->hasMany(TechnicalVisit::class);
    }
}