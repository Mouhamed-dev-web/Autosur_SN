<?php

namespace App\Enums;

enum ProblemType: string
{
    case ENGINE = 'engine';
    case BATTERY = 'battery';
    case TIRE = 'tire';
    case FUEL = 'fuel';
    case ACCIDENT = 'accident';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ENGINE => 'Panne moteur / ne démarre pas',
            self::BATTERY => 'Batterie déchargée',
            self::TIRE => 'Crevaison / pneu',
            self::FUEL => 'Panne de carburant',
            self::ACCIDENT => 'Accident (véhicule immobilisé)',
            self::OTHER => 'Autre problème',
        };
    }
}