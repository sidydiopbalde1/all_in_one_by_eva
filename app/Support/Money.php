<?php

namespace App\Support;

final class Money
{
    /** Formate un montant entier en francs CFA : 15000 → « 15 000 FCFA ». */
    public static function fcfa(int|float|null $montant): string
    {
        return number_format((float) $montant, 0, ',', "\u{202F}").' FCFA';
    }
}
