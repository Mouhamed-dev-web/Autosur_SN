<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case WAVE = 'wave';
    case ORANGE_MONEY = 'orange_money';

    public function label(): string
    {
        return match ($this) {
            self::WAVE => 'Wave',
            self::ORANGE_MONEY => 'Orange Money',
        };
    }
}