<?php

namespace App\Exceptions;

use DomainException;

/**
 * Violation d'une règle de gestion (créneau occupé, montant trop élevé…).
 * Rendue en redirection arrière avec un message lisible (voir bootstrap/app.php).
 */
class RegleMetierException extends DomainException
{
    public function __construct(string $message, public readonly string $champ = 'general')
    {
        parent::__construct($message);
    }
}
