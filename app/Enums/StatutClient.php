<?php

namespace App\Enums;

enum StatutClient: string
{
    case Actif = 'actif';
    case Inactif = 'inactif';
    case Archive = 'archive';

    public function label(): string
    {
        return match ($this) {
            self::Actif => 'Actif',
            self::Inactif => 'Inactif',
            self::Archive => 'Archivé',
        };
    }
}
