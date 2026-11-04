<?php

namespace App\Enums;

enum VisitStatus: string
{
    case REQUESTED = 'requested';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => 'Demandée',
            self::CONFIRMED => 'Confirmée',
            self::COMPLETED => 'Effectuée',
            self::CANCELLED => 'Annulée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::REQUESTED => 'bg-orange-100 text-orange-800',
            self::CONFIRMED => 'bg-blue-100 text-blue-800',
            self::COMPLETED => 'bg-emerald-100 text-emerald-800',
            self::CANCELLED => 'bg-gray-100 text-gray-600',
        };
    }

    /** Une visite « active » bloque une nouvelle demande pour le même véhicule. */
    public function isActive(): bool
    {
        return $this === self::REQUESTED || $this === self::CONFIRMED;
    }
}