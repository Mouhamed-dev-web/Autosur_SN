<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    public $timestamps = false;

    // Créé uniquement par AuditLog::record()
    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'details', 'ip'];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @param array<string, mixed> $details */
    public static function record(string $action, ?Model $subject = null, array $details = []): self
    {
        /** @var int|string|null $userId */
        $userId = Auth::id();

        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'details' => $details !== [] ? $details : null,
            'ip' => request()->ip(),
        ]);
    }
}