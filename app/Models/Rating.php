<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    // Créé uniquement par RatingService
    protected $fillable = [
        'user_id',
        'partner_id',
        'rateable_type',
        'rateable_id',
        'score',
        'comment',
        'is_hidden',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'is_hidden' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}