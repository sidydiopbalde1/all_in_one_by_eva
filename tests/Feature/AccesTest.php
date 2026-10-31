<?php

namespace Tests\Feature;

use App\Models\Paiement;
use Tests\TestCase;

class AccesTest extends TestCase
{
    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $this->get('/clients')->assertRedirect(route('login'));
    }

    public function test_connexion_et_deconnexion(): void
    {
        $user = $this->utilisateur();

        $this->post('/connexion', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->post('/deconnexion')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_un_compte_desactive_ne_peut_pas_se_connecter(): void
    {
        $user = $this->utilisateur();
        $user->update(['is_active' => false]);

        $this->post('/connexion', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_la_receptionniste_n_accede_pas_aux_pages_reservees_par_url(): void
    {
        $this->actingAs($this->utilisateur('receptionniste'));

        $this->get('/clients')->assertOk();
        $this->get('/rendez-vous')->assertOk();
        $this->get('/employes')->assertForbidden();
        $this->get('/parametres')->assertForbidden();
        $this->get('/clients/export')->assertForbidden();
        $this->get('/services/create')->assertForbidden();
    }

    public function test_la_receptionniste_ne_peut_pas_annuler_un_paiement(): void
    {
        $paiement = Paiement::factory()->create();
        $this->actingAs($this->utilisateur('receptionniste'));

        $this->patch(route('paiements.annuler', $paiement), ['motif' => 'Erreur de saisie'])->assertForbidden();
    }

    public function test_le_professionnel_voit_son_planning_mais_pas_les_clients(): void
    {
        $user = $this->utilisateur('professionnel');
        $this->employe()->update(['user_id' => $user->id]);
        $this->actingAs($user);

        $this->get('/')->assertRedirect(route('planning'));
        $this->get('/planning')->assertOk();
        $this->get('/clients')->assertForbidden();
        $this->get('/paiements')->assertForbidden();
    }

    public function test_l_administratrice_accede_a_tout(): void
    {
        $this->actingAs($this->utilisateur());

        foreach (['/', '/clients', '/rendez-vous', '/catalogue', '/paiements', '/employes', '/parametres'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
