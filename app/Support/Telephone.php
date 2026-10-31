<?php

namespace App\Support;

final class Telephone
{
    /** 221771234567 → « +221 77 123 45 67 » ; les autres numéros sont simplement préfixés de « + ». */
    public static function format(?string $numero): string
    {
        if (! $numero) {
            return '—';
        }

        if (preg_match('/^221(\d{2})(\d{3})(\d{2})(\d{2})$/', $numero, $m)) {
            return "+221 {$m[1]} {$m[2]} {$m[3]} {$m[4]}";
        }

        return '+'.$numero;
    }
}
