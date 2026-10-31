<?php

namespace App\Enums;

enum TypeRecompense: string
{
    case Reduction = 'reduction';
    case PrestationOfferte = 'prestation_offerte';
    case OffreSpeciale = 'offre_speciale';

    public function label(): string
    {
        return match ($this) {
            self::Reduction => 'Réduction',
            self::PrestationOfferte => 'Prestation offerte',
            self::OffreSpeciale => 'Offre spéciale',
        };
    }
}
