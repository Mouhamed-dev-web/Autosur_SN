<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Document extends Model
{
    public const DISK = 'local'; // disque privé : storage/app/private

    protected $fillable = [
        'vehicle_id',
        'type',
        'title',
        'path',
        'original_name',
        'mime',
        'size',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'size' => 'integer',
            'expires_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Supprimer l'enregistrement supprime aussi le fichier sur le disque
        static::deleting(function (Document $document) {
            Storage::disk(self::DISK)->delete($document->path);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function expiresSoon(): bool
    {
        return $this->expires_at !== null
            && ! $this->isExpired()
            && $this->expires_at->lte(now()->addDays(30));
    }

    public function downloadName(): string
    {
        return Str::slug($this->title) . '.' . pathinfo($this->path, PATHINFO_EXTENSION);
    }

    public function sizeForHumans(): string
    {
        return $this->size >= 1048576
            ? number_format($this->size / 1048576, 1, ',', ' ') . ' Mo'
            : number_format(max($this->size / 1024, 1), 0, ',', ' ') . ' Ko';
    }
}