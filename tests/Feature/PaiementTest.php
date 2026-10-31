<?php

namespace Tests\Feature;

use App\Enums\StatutRendezVous;
use App\Models\MouvementPoints;
use App\Models\Paiement;
use App\Models\RendezVous;
use App\Services\StatistiquesService;
use Tests\TestCase;

class PaiementTest extends TestCase
{
    private RendezVous $rdv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->utilisateur());
        $this->post(route('rendez-vous.store'), $this->donneesRdv($this->employe(), $this->client(), $this->service(15000)));
        $this->rdv = RendezVous::sole();
    }

    private function payer(int $montant)
    {
        return $this->post(route('paiements.store', $this->rdv), ['montant' => $montant, 'mode_paiement_id' => $this->modePaiement()->id]);
    }

    public function test_paiement_partiel_puis_solde(): void
    {
        $this->payer(5000)->assertRedirect();
        $this->assertSame(10000, $this->rdv->fresh()->resteAPayer());
        $this->assertSame(10000, Paiement::sole()->reste_a_payer);

        $this->payer(10000);
        $this->assertTrue($this->rdv->fresh()->estSolde());
    }

    public function test_accepte_un_montant_saisi_avec_espaces(): void
    {
        $this->post(route('paiements.store', $this->rdv), ['montant' => '15 000', 'mode_paiement_id' => $this->modePaiement()->id])
            ->assertSessionHasNoErrors();

        $this->assertSame(15000, Paiement::sole()->montant_paye);
    }

    public function test_refuse_un_montant_superieur_au_reste(): void
    {
        $this->payer(20000)->assertSessionHasErrors('montant');
        $this->assertSame(0, Paiement::count());
    }

    public function test_points_credites_une_seule_fois_quand_termine_et_solde(): void
    {
        $this->payer(15000);
        $this->assertSame(0, MouvementPoints::count(), 'Pas de points tant que la prestation n\'est pas terminée.');

        $this->patch(route('rendez-vous.statut', $this->rdv), ['statut' => 'termine']);
        $this->patch(route('rendez-vous.statut', $this->rdv), ['statut' => 'en_cours']);
        $this->patch(route('rendez-vous.statut', $this->rdv), ['statut' => 'termine']);

        $this->assertSame(1, MouvementPoints::count());
        $this->assertSame(15, $this->rdv->client->soldePoints()); // 1 point / 1 000 FCFA
    }

    public function test_aucun_point_pour_une_prestation_non_payee(): void
    {
        $this->payer(5000);
        $this->patch(route('rendez-vous.statut', $this->rdv), ['statut' => 'termine']);

        $this->assertSame(0, $this->rdv->client->soldePoints());
    }

    public function test_annulation_exclut_le_paiement_des_recettes_et_retire_les_points(): void
    {
        $this->payer(15000);
        $this->patch(route('rendez-vous.statut', $this->rdv), ['statut' => 'termine']);
        $paiement = Paiement::sole();

        $this->patch(route('paiements.annuler', $paiement), ['motif' => 'Erreur de mode de paiement'])->assertSessionHasNoErrors();

        $this->assertNotNull($paiement->fresh()->annule_le);
        $this->assertSame(0, app(StatistiquesService::class)->chiffreAffaires(now()->startOfDay(), now()->endOfDay()));
        $this->assertSame(0, $this->rdv->client->soldePoints());
        $this->assertSame(15000, $this->rdv->fresh()->resteAPayer());
        $this->assertDatabaseCount('mouvements_points', 2); // gain + correction : l'historique est conservé
    }

    public function test_impossible_d_encaisser_un_rendez_vous_annule(): void
    {
        $this->rdv->update(['statut' => StatutRendezVous::Annule]);

        $this->payer(15000)->assertSessionHasErrors('general');
    }
}
