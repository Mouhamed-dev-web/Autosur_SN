<?php

namespace App\Enums;

enum DocumentType: string
{
    case CARTE_GRISE = 'carte_grise';
    case ASSURANCE = 'assurance';
    case VISITE_TECHNIQUE = 'visite_technique';
    case FACTURE = 'facture';
    case AUTRE = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::CARTE_GRISE => 'Carte grise',
            self::ASSURANCE => 'Attestation d\'assurance',
            self::VISITE_TECHNIQUE => 'Visite technique',
            self::FACTURE => 'Facture',
            self::AUTRE => 'Autre',
        };
    }
}