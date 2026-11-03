<?php

namespace App\Enums;

enum InsuranceStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case NONE = 'none';
    case UNKNOWN = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Assuré',
            self::EXPIRED => 'Assurance expirée',
            self::NONE => 'Non assuré',
            self::UNKNOWN => 'Non renseigné',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-100 text-emerald-800',
            self::EXPIRED => 'bg-red-100 text-red-800',
            self::NONE => 'bg-orange-100 text-orange-800',
            self::UNKNOWN => 'bg-gray-100 text-gray-700',
        };
    }
}