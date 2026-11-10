<?php

namespace App\Models;

use App\Enums\ProblemType;
use App\Enums\SosStatus;
use App\Services\SosService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class SosRequest extends Model
{
    protected $table = 'sos_requests';

    protected $fillable = [
        'vehicle_id',
        'partner_id',
        'status',
        'problem',
        'description',
        'lat',
        'lng',
        'location_text',
        'accepted_at',
        'arrived_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SosStatus::class,
            'problem' => ProblemType::class,
            'lat' => 'float',
            'lng' => 'float',
            'accepted_at' => 'datetime',
            'arrived_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /** Dépanneurs qui ont refusé cette demande. */
    public function declinedBy(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'sos_request_declines')->withTimestamps();
    }

    public function hasCoordinates(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [SosStatus::PENDING, SosStatus::ACCEPTED], true);
    }

    /** Demande en recherche depuis trop longtemps : plus diffusée aux dépanneurs. */
    public function isStale(): bool
    {
        return $this->status === SosStatus::PENDING
            && $this->created_at->lt(now()->subMinutes(SosService::PENDING_TTL_MINUTES));
    }
    public function rating(): MorphOne
    {
        return $this->morphOne(Rating::class, 'rateable');
    }
}