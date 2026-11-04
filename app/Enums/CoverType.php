<?php

namespace App\Enums;

enum CoverType: string
{
    case TIERS = 'tiers';
    case TIERS_PLUS = 'tiers_plus';
    case TOUS_RISQUES = 'tous_risques';

    public function label(): string
    {
        return match ($this) {
            self::TIERS => 'Responsabilité civile (tiers)',
            self::TIERS_PLUS => 'Tiers étendu (vol, incendie, bris de glace)',
            self::TOUS_RISQUES => 'Tous risques',
        };
    }

    public function needsValue(): bool
    {
        return $this === self::TOUS_RISQUES;
    }
}