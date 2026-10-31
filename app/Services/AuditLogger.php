<?php

namespace App\Services;

use App\Models\HistoriqueAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AuditLogger
{
    private const CHAMPS_MASQUES = ['password', 'remember_token'];

    public function log(string $action, ?Model $cible = null, array $anciennes = [], array $nouvelles = []): void
    {
        HistoriqueAction::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $cible?->getMorphClass(),
            'auditable_id' => $cible?->getKey(),
            'anciennes_valeurs' => Arr::except($anciennes, self::CHAMPS_MASQUES) ?: null,
            'nouvelles_valeurs' => Arr::except($nouvelles, self::CHAMPS_MASQUES) ?: null,
            'ip' => request()?->ip(),
        ]);
    }

    /** Journalise uniquement les attributs modifiés d'un modèle déjà sauvegardé. */
    public function logChangements(string $action, Model $modele): void
    {
        $nouvelles = $modele->getChanges();
        unset($nouvelles['updated_at']);

        if ($nouvelles === []) {
            return;
        }

        $anciennes = array_intersect_key($modele->getOriginal(), $nouvelles);
        $this->log($action, $modele, $anciennes, $nouvelles);
    }
}
