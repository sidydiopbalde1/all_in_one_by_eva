<?php

namespace App\Enums;

enum TypeMouvementPoints: string
{
    case Gain = 'gain';
    case Utilisation = 'utilisation';
    case Correction = 'correction';

    public function label(): string
    {
        return match ($this) {
            self::Gain => 'Gain',
            self::Utilisation => 'Utilisation',
            self::Correction => 'Correction',
        };
    }
}
