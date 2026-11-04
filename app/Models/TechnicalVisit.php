<?php

namespace App\Models;

use App\Enums\VisitStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicalVisit extends Model
{
    protected $fillable = [
        'vehicle_id',
        'scheduled_at',
        'status',
        'fee_amount',
        'fee_paid_at',
        'notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'status' => VisitStatus::class,
            'fee_amount' => 'integer',
            'fee_paid_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function canBeCancelled(): bool
    {
        return $this->status->isActive() && $this->scheduled_at->isFuture();
    }

    public function feeForHumans(): string
    {
        return number_format($this->fee_amount, 0, ',', ' ').' FCFA';
    }
}