<?php

namespace App\Services;

use App\Enums\StatutRendezVous;
use App\Enums\ZoneSalon;
use App\Exceptions\RegleMetierException;
use App\Models\Employe;
use App\Models\Forfait;
use App\Models\ParametreSalon;
use App\Models\RendezVous;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RendezVousService
{
    public function __construct(
        private readonly DisponibiliteService $disponibilites,
        private readonly FideliteService $fidelite,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array{client_id:int, employe_id:int, date:string, heure_debut:string, zone:string,
     *               services?:array<int>, forfaits?:array<int>, commentaire?:?string}  $donnees
     */
    public function creer(array $donnees, User $auteur): RendezVous
    {
        return DB::transaction(function () use ($donnees, $auteur) {
            [$employe, $lignes, $debut, $fin] = $this->preparer($donnees);

            $rendezVous = RendezVous::create([
                'client_id' => $donnees['client_id'],
                'employe_id' => $employe->id,
                'debut' => $debut,
                'fin' => $fin,
                'zone' => $donnees['zone'],
                'prix_prevu' => $lignes->sum('prix'),
                'statut' => StatutRendezVous::EnAttente,
                'commentaire' => $donnees['commentaire'] ?? null,
                'cree_par' => $auteur->id,
            ]);
            $rendezVous->lignes()->createMany($lignes->all());

            $this->audit->log('rdv.cree', $rendezVous, nouvelles: $rendezVous->only(['debut', 'fin', 'employe_id', 'prix_prevu']));

            return $rendezVous;
        });
    }

    public function mettreAJour(RendezVous $rendezVous, array $donnees): RendezVous
    {
        if (in_array($rendezVous->statut, [StatutRendezVous::Termine, StatutRendezVous::Annule], true)) {
            throw new RegleMetierException('Un rendez-vous terminé ou annulé ne peut plus être modifié.');
        }

        return DB::transaction(function () use ($rendezVous, $donnees) {
            [$employe, $lignes, $debut, $fin] = $this->preparer($donnees, $rendezVous->id);

            $rendezVous->update([
                'client_id' => $donnees['client_id'],
                'employe_id' => $employe->id,
                'debut' => $debut,
                'fin' => $fin,
                'zone' => $donnees['zone'],
                'prix_prevu' => $lignes->sum('prix'),
                'commentaire' => $donnees['commentaire'] ?? null,
            ]);
            $rendezVous->lignes()->delete();
            $rendezVous->lignes()->createMany($lignes->all());

            $this->audit->logChangements('rdv.modifie', $rendezVous);

            return $rendezVous;
        });
    }

    public function changerStatut(RendezVous $rendezVous, StatutRendezVous $statut): RendezVous
    {
        $ancien = $rendezVous->statut;

        if ($ancien === $statut) {
            return $rendezVous;
        }

        if (in_array($ancien, [StatutRendezVous::Annule, StatutRendezVous::Absent], true)
            && ! $this->creneauToujoursLibre($rendezVous)) {
            throw new RegleMetierException('Le créneau a été réattribué : impossible de réactiver ce rendez-vous.');
        }

        DB::transaction(function () use ($rendezVous, $statut, $ancien) {
            $rendezVous->update(['statut' => $statut]);
            $this->audit->log('rdv.statut', $rendezVous, ['statut' => $ancien->value], ['statut' => $statut->value]);

            if ($statut === StatutRendezVous::Termine) {
                $this->fidelite->crediterSiEligible($rendezVous);
            }
        });

        return $rendezVous;
    }

    /**
     * @return array{0: Employe, 1: Collection<int, array>, 2: CarbonImmutable, 3: CarbonImmutable}
     */
    private function preparer(array $donnees, ?int $ignorerId = null): array
    {
        $employe = Employe::query()->lockForUpdate()->findOrFail($donnees['employe_id']);
        $lignes = $this->lignes($donnees['services'] ?? [], $donnees['forfaits'] ?? [], ZoneSalon::from($donnees['zone']));

        if ($lignes->isEmpty()) {
            throw new RegleMetierException('Choisissez au moins une prestation ou un forfait.', 'services');
        }

        $debut = CarbonImmutable::parse("{$donnees['date']} {$donnees['heure_debut']}");
        $fin = $debut->addMinutes($lignes->sum('duree_minutes') + (int) ParametreSalon::get('pause_minutes', 0));

        $this->disponibilites->verifier($employe, $debut, $fin, $ignorerId);

        return [$employe, $lignes, $debut, $fin];
    }

    /** @return Collection<int, array{service_id:?int, forfait_id:?int, libelle:string, prix:int, duree_minutes:int}> */
    private function lignes(array $serviceIds, array $forfaitIds, ZoneSalon $zone): Collection
    {
        $services = Service::query()->actifs()->whereIn('id', $serviceIds)->get();

        if ($services->contains(fn (Service $s) => $s->zone !== $zone)) {
            throw new RegleMetierException("Certaines prestations ne sont pas proposées en « {$zone->label()} ».", 'zone');
        }

        $forfaits = Forfait::query()->actifs()->with('services')->whereIn('id', $forfaitIds)->get();

        return $services->map(fn (Service $s) => [
            'service_id' => $s->id, 'forfait_id' => null,
            'libelle' => $s->nom, 'prix' => $s->prix, 'duree_minutes' => $s->duree_minutes,
        ])->concat($forfaits->map(fn (Forfait $f) => [
            'service_id' => null, 'forfait_id' => $f->id,
            'libelle' => "Forfait {$f->nom}", 'prix' => $f->prix, 'duree_minutes' => $f->dureeTotale(),
        ]))->values();
    }

    private function creneauToujoursLibre(RendezVous $rendezVous): bool
    {
        return ! $this->disponibilites->aUnChevauchement($rendezVous->employe, $rendezVous->debut, $rendezVous->fin, $rendezVous->id);
    }
}
