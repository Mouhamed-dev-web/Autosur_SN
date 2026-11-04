<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case REQUESTED = 'requested';
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => 'Demande en cours',
            self::ACTIVE => 'Souscrite',
            self::CANCELLED => 'Annulée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::REQUESTED => 'bg-orange-100 text-orange-800',
            self::ACTIVE => 'bg-emerald-100 text-emerald-800',
            self::CANCELLED => 'bg-gray-100 text-gray-600',
        };
    }
}