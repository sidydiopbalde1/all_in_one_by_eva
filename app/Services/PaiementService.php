<?php

namespace App\Services;

use App\Enums\StatutRendezVous;
use App\Exceptions\RegleMetierException;
use App\Models\Paiement;
use App\Models\RendezVous;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaiementService
{
    public function __construct(
        private readonly FideliteService $fidelite,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * Enregistre un paiement (complet ou partiel) de façon atomique.
     *
     * @param  array{mode_paiement_id:int, montant:int, reference?:?string, paye_le?:?string}  $donnees
     */
    public function enregistrer(RendezVous $rendezVous, array $donnees, User $auteur): Paiement
    {
        return DB::transaction(function () use ($rendezVous, $donnees, $auteur) {
            $rendezVous = RendezVous::query()->lockForUpdate()->findOrFail($rendezVous->id);

            if (in_array($rendezVous->statut, [StatutRendezVous::Annule, StatutRendezVous::Absent], true)) {
                throw new RegleMetierException('Impossible d\'encaisser un rendez-vous annulé ou manqué.');
            }

            $montant = (int) $donnees['montant'];
            $reste = $rendezVous->resteAPayer();

            if ($montant > $reste) {
                throw new RegleMetierException('Le montant dépasse le reste à payer ('.number_format($reste, 0, ',', ' ').' FCFA).', 'montant');
            }

            $paiement = Paiement::create([
                'client_id' => $rendezVous->client_id,
                'rendez_vous_id' => $rendezVous->id,
                'mode_paiement_id' => $donnees['mode_paiement_id'],
                'montant_total' => $rendezVous->prix_prevu,
                'montant_paye' => $montant,
                'reste_a_payer' => $reste - $montant,
                'paye_le' => $donnees['paye_le'] ?? now(),
                'reference' => $donnees['reference'] ?? null,
                'enregistre_par' => $auteur->id,
            ]);

            $this->audit->log('paiement.cree', $paiement, nouvelles: $paiement->only(['montant_paye', 'reste_a_payer', 'mode_paiement_id']));
            $this->fidelite->crediterSiEligible($rendezVous);

            return $paiement;
        });
    }

    /** Annule un paiement sans le supprimer : il reste visible mais ne compte plus dans les recettes. */
    public function annuler(Paiement $paiement, string $motif, User $auteur): Paiement
    {
        if ($paiement->estAnnule()) {
            throw new RegleMetierException('Ce paiement est déjà annulé.');
        }

        return DB::transaction(function () use ($paiement, $motif, $auteur) {
            $paiement->update(['annule_le' => now(), 'annule_par' => $auteur->id, 'motif_annulation' => $motif]);
            $this->fidelite->annulerGainDuPaiement($paiement->id, $auteur);
            $this->audit->log('paiement.annule', $paiement, nouvelles: ['motif' => $motif]);

            return $paiement;
        });
    }
}
