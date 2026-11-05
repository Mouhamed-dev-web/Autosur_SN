<?php

namespace App\Enums;

enum SosStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case ARRIVED = 'arrived';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En recherche d\'un dépanneur',
            self::ACCEPTED => 'Dépanneur en route',
            self::ARRIVED => 'Dépanneur sur place',
            self::COMPLETED => 'Terminée',
            self::CANCELLED => 'Annulée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'bg-orange-100 text-orange-800',
            self::ACCEPTED => 'bg-blue-100 text-blue-800',
            self::ARRIVED => 'bg-indigo-100 text-indigo-800',
            self::COMPLETED => 'bg-emerald-100 text-emerald-800',
            self::CANCELLED => 'bg-gray-100 text-gray-600',
        };
    }

    /** Une demande « ouverte » bloque une nouvelle demande du même client. */
    public function isOpen(): bool
    {
        return in_array($this, [self::PENDING, self::ACCEPTED, self::ARRIVED], true);
    }

    /** @return list<string> */
    public static function openValues(): array
    {
        return [self::PENDING->value, self::ACCEPTED->value, self::ARRIVED->value];
    }
}