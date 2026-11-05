<?php

namespace App\Enums;

enum PartnerType: string
{
    case GARAGE = 'garage';
    case DEPANNEUR = 'depanneur';
    case ASSURANCE = 'assurance';

    public function label(): string
    {
        return match ($this) {
            self::GARAGE => 'Garage',
            self::DEPANNEUR => 'Dépanneur',
            self::ASSURANCE => 'Assureur',
        };
    }
}