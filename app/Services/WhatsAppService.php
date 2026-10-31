<?php

namespace App\Services;

use App\Enums\TypeMessage;
use App\Models\Client;
use App\Models\ModeleMessage;
use InvalidArgumentException;

/**
 * Prépare des liens officiels wa.me avec un message prérempli.
 * Aucun envoi automatique : l'utilisateur relit et envoie lui-même le message.
 */
class WhatsAppService
{
    private const INDICATIF_SENEGAL = '221';

    /**
     * Normalise un numéro au format international sans « + » (ex. 221771234567).
     * Gère « +221 », « 00221 », « 221 » et les numéros locaux à 9 chiffres sans doubler l'indicatif.
     */
    public function normaliserNumero(string $numero): string
    {
        $chiffres = preg_replace('/\D/', '', $numero);

        if (str_starts_with($chiffres, '00')) {
            $chiffres = substr($chiffres, 2);
        }

        if (strlen($chiffres) === 9 && preg_match('/^[37]/', $chiffres)) {
            return self::INDICATIF_SENEGAL.$chiffres;
        }

        if (str_starts_with($chiffres, self::INDICATIF_SENEGAL) && strlen($chiffres) === 12) {
            return $chiffres;
        }

        if (strlen($chiffres) >= 10 && strlen($chiffres) <= 15 && ! str_starts_with($chiffres, '0')) {
            return $chiffres; // numéro étranger déjà au format international
        }

        throw new InvalidArgumentException("Numéro WhatsApp invalide : {$numero}");
    }

    public function numeroValide(?string $numero): bool
    {
        if (! $numero) {
            return false;
        }

        try {
            $this->normaliserNumero($numero);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /** Remplace les variables [Prénom], [date], [heure], [prestation]… dans un texte. */
    public function remplir(string $contenu, array $variables): string
    {
        $remplacements = [];
        foreach ($variables as $cle => $valeur) {
            $remplacements["[{$cle}]"] = (string) $valeur;
        }

        return strtr($contenu, $remplacements);
    }

    public function messagePour(TypeMessage $type, Client $client, array $variables = []): string
    {
        $modele = ModeleMessage::query()->where('type', $type)->firstOrFail();

        return $this->remplir($modele->contenu, ['Prénom' => $client->prenom, ...$variables]);
    }

    public function lien(string $numero, string $message): string
    {
        return 'https://wa.me/'.$this->normaliserNumero($numero).'?text='.rawurlencode($message);
    }
}
