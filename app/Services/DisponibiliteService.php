<?php

namespace App\Services;

use App\Exceptions\RegleMetierException;
use App\Models\Employe;
use App\Models\RendezVous;
use Carbon\CarbonInterface;

class DisponibiliteService
{
    /**
     * Vérifie qu'un créneau est libre pour le professionnel.
     *
     * @throws RegleMetierException
     */
    public function verifier(Employe $employe, CarbonInterface $debut, CarbonInterface $fin, ?int $ignorerRendezVousId = null): void
    {
        if (! $employe->is_active) {
            throw new RegleMetierException("{$employe->nom_complet} n'est plus actif(ve).", 'employe_id');
        }

        if (! $debut->isSameDay($fin)) {
            throw new RegleMetierException('Le rendez-vous doit se terminer le même jour.', 'heure_debut');
        }

        if (! $this->dansHorairesTravail($employe, $debut, $fin)) {
            throw new RegleMetierException(
                "Ce créneau est en dehors des horaires de travail de {$employe->nom_complet}.",
                'heure_debut',
            );
        }

        if ($this->aUnChevauchement($employe, $debut, $fin, $ignorerRendezVousId)) {
            throw new RegleMetierException(
                "{$employe->nom_complet} a déjà un rendez-vous sur ce créneau.",
                'heure_debut',
            );
        }
    }

    public function dansHorairesTravail(Employe $employe, CarbonInterface $debut, CarbonInterface $fin): bool
    {
        $disponibilite = $employe->disponibilites()->where('jour_semaine', $debut->isoWeekday())->first();

        if (! $disponibilite) {
            return false;
        }

        return $debut->format('H:i:s') >= $this->heure($disponibilite->heure_debut)
            && $fin->format('H:i:s') <= $this->heure($disponibilite->heure_fin);
    }

    public function aUnChevauchement(Employe $employe, CarbonInterface $debut, CarbonInterface $fin, ?int $ignorerRendezVousId = null): bool
    {
        return RendezVous::query()
            ->where('employe_id', $employe->id)
            ->bloquants()
            ->where('debut', '<', $fin)
            ->where('fin', '>', $debut)
            ->when($ignorerRendezVousId, fn ($q) => $q->whereKeyNot($ignorerRendezVousId))
            ->exists();
    }

    private function heure(string $heure): string
    {
        return strlen($heure) === 5 ? "{$heure}:00" : $heure;
    }
}
