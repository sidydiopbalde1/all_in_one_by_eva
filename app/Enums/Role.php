<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Receptionniste = 'receptionniste';
    case Professionnel = 'professionnel';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administratrice',
            self::Receptionniste => 'Réceptionniste',
            self::Professionnel => 'Professionnel(le)',
        };
    }
}
