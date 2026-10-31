<?php

namespace App\Enums;

enum TypeMessage: string
{
    case Anniversaire = 'anniversaire';
    case Promotion = 'promotion';
    case RappelRendezVous = 'rappel_rdv';
    case Fidelisation = 'fidelisation';

    public function label(): string
    {
        return match ($this) {
            self::Anniversaire => 'Anniversaire',
            self::Promotion => 'Promotion',
            self::RappelRendezVous => 'Rappel de rendez-vous',
            self::Fidelisation => 'Fidélisation',
        };
    }
}
