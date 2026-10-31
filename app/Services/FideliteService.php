<?php

namespace App\Services;

use App\Enums\StatutRendezVous;
use App\Enums\TypeMouvementPoints;
use App\Exceptions\RegleMetierException;
use App\Models\Client;
use App\Models\MouvementPoints;
use App\Models\ParametreSalon;
use App\Models\Recompense;
use App\Models\RendezVous;
use App\Models\User;
use App\Models\UtilisationRecompense;
use Illuminate\Support\Facades\DB;

class FideliteService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** Points accordés : 1 point par tranche configurée (par défaut 1 000 FCFA). */
    public function pointsPour(int $montant): int
    {
        $tranche = max(1, (int) ParametreSalon::get('fidelite_fcfa_par_point', 1000));

        return intdiv($montant, $tranche);
    }

    /**
     * Crédite les points d'un rendez-vous terminé ET entièrement réglé.
     * Idempotent : le mouvement est rattaché au dernier paiement (contrainte d'unicité sur paiement_id).
     */
    public function crediterSiEligible(RendezVous $rendezVous): ?MouvementPoints
    {
        if ($rendezVous->statut !== StatutRendezVous::Termine || ! $rendezVous->estSolde()) {
            return null;
        }

        $paiements = $rendezVous->paiements()->valides()->latest('paye_le')->latest('id')->get();

        if ($paiements->isEmpty() || MouvementPoints::query()->whereIn('paiement_id', $paiements->pluck('id'))->exists()) {
            return null;
        }

        $points = $this->pointsPour($paiements->sum('montant_paye'));

        if ($points === 0) {
            return null;
        }

        return MouvementPoints::create([
            'client_id' => $rendezVous->client_id,
            'paiement_id' => $paiements->first()->id,
            'type' => TypeMouvementPoints::Gain,
            'points' => $points,
            'motif' => "Rendez-vous du {$rendezVous->debut->format('d/m/Y')}",
        ]);
    }

    /** Retire les points gagnés grâce à un paiement annulé (mouvement compensatoire, l'historique est conservé). */
    public function annulerGainDuPaiement(int $paiementId, User $auteur): void
    {
        $gain = MouvementPoints::query()->where('paiement_id', $paiementId)->first();

        if ($gain) {
            $this->corriger($gain->client, -$gain->points, "Annulation du paiement #{$paiementId}", $auteur);
        }
    }

    public function utiliserRecompense(Client $client, Recompense $recompense, User $auteur): UtilisationRecompense
    {
        return DB::transaction(function () use ($client, $recompense, $auteur) {
            Client::query()->whereKey($client->id)->lockForUpdate()->first();

            if (! $recompense->is_active) {
                throw new RegleMetierException('Cette récompense n\'est plus disponible.');
            }

            if ($client->soldePoints() < $recompense->seuil_points) {
                throw new RegleMetierException('Solde de points insuffisant pour cette récompense.');
            }

            $mouvement = MouvementPoints::create([
                'client_id' => $client->id,
                'type' => TypeMouvementPoints::Utilisation,
                'points' => -$recompense->seuil_points,
                'motif' => "Récompense : {$recompense->libelle}",
                'enregistre_par' => $auteur->id,
            ]);

            $utilisation = UtilisationRecompense::create([
                'client_id' => $client->id,
                'recompense_id' => $recompense->id,
                'mouvement_points_id' => $mouvement->id,
                'enregistre_par' => $auteur->id,
                'utilisee_le' => now(),
            ]);

            $this->audit->log('fidelite.recompense', $client, nouvelles: ['recompense' => $recompense->libelle]);

            return $utilisation;
        });
    }

    public function corriger(Client $client, int $points, string $motif, User $auteur): MouvementPoints
    {
        if ($points === 0) {
            throw new RegleMetierException('La correction doit être différente de zéro.', 'points');
        }

        if ($client->soldePoints() + $points < 0) {
            throw new RegleMetierException('La correction rendrait le solde négatif.', 'points');
        }

        $mouvement = MouvementPoints::create([
            'client_id' => $client->id,
            'type' => TypeMouvementPoints::Correction,
            'points' => $points,
            'motif' => $motif,
            'enregistre_par' => $auteur->id,
        ]);

        $this->audit->log('fidelite.correction', $client, nouvelles: ['points' => $points, 'motif' => $motif]);

        return $mouvement;
    }
}
