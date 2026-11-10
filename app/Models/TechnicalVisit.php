<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Enums\VisitStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
        'partner_id',
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

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'payable');
    }

    public function isPaid(): bool
    {
        return $this->fee_paid_at !== null;
    }

    public function canBePaid(): bool
    {
        return ! $this->isPaid() && $this->status !== VisitStatus::CANCELLED;
    }
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function rating(): MorphOne
    {
        return $this->morphOne(Rating::class, 'rateable');
    }
}