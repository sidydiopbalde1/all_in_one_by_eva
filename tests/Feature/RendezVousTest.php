<?php

namespace Tests\Feature;

use App\Enums\StatutRendezVous;
use App\Models\ParametreSalon;
use App\Models\RendezVous;
use Tests\TestCase;

class RendezVousTest extends TestCase
{
    public function test_cree_un_rendez_vous_et_calcule_fin_et_prix(): void
    {
        $this->actingAs($this->utilisateur('receptionniste'));
        [$employe, $client] = [$this->employe(), $this->client()];
        $coupe = $this->service(5000, 30);
        $soin = $this->service(7000, 45);

        $this->post(route('rendez-vous.store'), [...$this->donneesRdv($employe, $client, $coupe), 'services' => [$coupe->id, $soin->id]])
            ->assertRedirect();

        $rdv = RendezVous::sole();
        $this->assertSame('2026-11-03 11:15', $rdv->fin->format('Y-m-d H:i'));
        $this->assertSame(12000, $rdv->prix_prevu);
        $this->assertSame(StatutRendezVous::EnAttente, $rdv->statut);
        $this->assertCount(2, $rdv->lignes);
    }

    public function test_ajoute_la_pause_configuree_a_la_duree(): void
    {
        ParametreSalon::set('pause_minutes', 15);
        $this->actingAs($this->utilisateur());
        $service = $this->service(5000, 30);

        $this->post(route('rendez-vous.store'), $this->donneesRdv($this->employe(), $this->client(), $service));

        $this->assertSame('10:45', RendezVous::sole()->fin->format('H:i'));
    }

    public function test_refuse_une_double_reservation_qui_chevauche(): void
    {
        $this->actingAs($this->utilisateur());
        [$employe, $service] = [$this->employe(), $this->service(10000, 60)];

        $this->post(route('rendez-vous.store'), $this->donneesRdv($employe, $this->client(), $service, heure: '10:00'));
        $this->post(route('rendez-vous.store'), $this->donneesRdv($employe, $this->client(), $service, heure: '10:30'))
            ->assertSessionHasErrors('heure_debut');

        $this->assertSame(1, RendezVous::count());
    }

    public function test_accepte_un_creneau_contigu(): void
    {
        $this->actingAs($this->utilisateur());
        [$employe, $service] = [$this->employe(), $this->service(10000, 60)];

        $this->post(route('rendez-vous.store'), $this->donneesRdv($employe, $this->client(), $service, heure: '10:00'));
        $this->post(route('rendez-vous.store'), $this->donneesRdv($employe, $this->client(), $service, heure: '11:00'))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, RendezVous::count());
    }

    public function test_un_rendez_vous_annule_libere_le_creneau(): void
    {
        $this->actingAs($this->utilisateur());
        [$employe, $service] = [$this->employe(), $this->service()];

        $this->post(route('rendez-vous.store'), $this->donneesRdv($employe, $this->client(), $service));
        $this->patch(route('rendez-vous.statut', RendezVous::sole()), ['statut' => 'annule']);

        $this->post(route('rendez-vous.store'), $this->donneesRdv($employe, $this->client(), $service))->assertSessionHasNoErrors();
        $this->assertSame(2, RendezVous::count());
    }

    public function test_refuse_un_creneau_hors_horaires_de_travail(): void
    {
        $this->actingAs($this->utilisateur());
        $service = $this->service(10000, 60);

        $this->post(route('rendez-vous.store'), $this->donneesRdv($this->employe(), $this->client(), $service, heure: '18:30'))
            ->assertSessionHasErrors('heure_debut');
        $this->post(route('rendez-vous.store'), $this->donneesRdv($this->employe(), $this->client(), $service, date: '2026-11-08'))
            ->assertSessionHasErrors('heure_debut'); // dimanche : jour non travaillé
    }

    public function test_seule_l_administratrice_peut_creer_dans_le_passe(): void
    {
        [$employe, $service] = [$this->employe(), $this->service()];
        $passe = $this->donneesRdv($employe, $this->client(), $service, date: '2026-10-27');

        $this->actingAs($this->utilisateur('receptionniste'))
            ->post(route('rendez-vous.store'), $passe)->assertSessionHasErrors('heure_debut');

        $this->actingAs($this->utilisateur())
            ->post(route('rendez-vous.store'), $passe)->assertSessionHasNoErrors();
    }

    public function test_deplacer_un_rendez_vous_ignore_son_propre_creneau(): void
    {
        $this->actingAs($this->utilisateur());
        [$employe, $service] = [$this->employe(), $this->service(10000, 60)];
        $this->post(route('rendez-vous.store'), $this->donneesRdv($employe, $this->client(), $service, heure: '10:00'));
        $rdv = RendezVous::sole();

        $this->put(route('rendez-vous.update', $rdv), $this->donneesRdv($employe, $rdv->client, $service, heure: '10:30'))
            ->assertSessionHasNoErrors();

        $this->assertSame('10:30', $rdv->fresh()->debut->format('H:i'));
    }

    public function test_le_professionnel_ne_modifie_que_ses_propres_rendez_vous(): void
    {
        $this->actingAs($this->utilisateur());
        [$moi, $collegue, $service] = [$this->employe(), $this->employe(), $this->service()];
        $this->post(route('rendez-vous.store'), $this->donneesRdv($moi, $this->client(), $service));
        $this->post(route('rendez-vous.store'), $this->donneesRdv($collegue, $this->client(), $service));
        [$mien, $autre] = RendezVous::orderBy('id')->get();

        $pro = $this->utilisateur('professionnel');
        $moi->update(['user_id' => $pro->id]);
        $this->actingAs($pro);

        $this->patch(route('rendez-vous.statut', $mien), ['statut' => 'en_cours'])->assertRedirect();
        $this->patch(route('rendez-vous.statut', $autre), ['statut' => 'en_cours'])->assertForbidden();
        $this->get(route('rendez-vous.show', $autre))->assertForbidden();
    }
}
