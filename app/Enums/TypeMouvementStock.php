<?php

namespace App\Enums;

enum TypeMouvementStock: string
{
    case Entree = 'entree';
    case Sortie = 'sortie';
    case Consommation = 'consommation';

    public function label(): string
    {
        return match ($this) {
            self::Entree => 'Approvisionnement',
            self::Sortie => 'Sortie',
            self::Consommation => 'Consommation prestation',
        };
    }

    public function estUneEntree(): bool
    {
        return $this === self::Entree;
    }
}
