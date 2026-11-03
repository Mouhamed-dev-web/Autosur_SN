<?php

namespace App\Models;

use App\Enums\ReminderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'document_id',
        'type',
        'due_date',
        'days_before',
        'dedupe_key',
    ];

    protected function casts(): array
    {
        return [
            'type' => ReminderType::class,
            'due_date' => 'date',
            'days_before' => 'integer',
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

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function subjectLabel(): string
    {
        return $this->type === ReminderType::DOCUMENT
            ? ($this->document?->title ?? 'Document')
            : $this->type->label();
    }

    public function message(): string
    {
        $plate = $this->vehicle ? ' du véhicule '.$this->vehicle->plate : '';
        $date = $this->due_date->format('d/m/Y');

        return $this->days_before === 0
            ? "{$this->subjectLabel()}{$plate} : échéance aujourd'hui ({$date})."
            : "{$this->subjectLabel()}{$plate} : échéance dans moins de {$this->days_before} jours ({$date}).";
    }
}