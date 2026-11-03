<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case CLIENT = 'client';
    case GARAGE = 'garage';
    case DEPANNEUR = 'depanneur';
    case ASSURANCE = 'assurance';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrateur',
            self::CLIENT => 'Automobiliste',
            self::GARAGE => 'Garage partenaire',
            self::DEPANNEUR => 'Dépanneur partenaire',
            self::ASSURANCE => 'Partenaire assurance',
        };
    }
}