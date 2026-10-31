<?php

namespace Tests\Feature;

use App\Enums\StatutClient;
use App\Models\Client;
use App\Models\Recompense;
use Tests\TestCase;

class ClientTest extends TestCase
{
    private function donnees(array $surcharge = []): array
    {
        return ['prenom' => 'Awa', 'nom' => 'Diop', 'telephone' => '77 123 45 67', 'preference_communication' => 'whatsapp', ...$surcharge];
    }

    public function test_cree_un_client_avec_numero_normalise(): void
    {
        $this->actingAs($this->utilisateur('receptionniste'));

        $this->post(route('clients.store'), $this->donnees(['consentement_promo' => '1']))->assertRedirect();

        $client = Client::sole();
        $this->assertSame('221771234567', $client->telephone);
        $this->assertTrue($client->consentement_promo);
        $this->assertNotNull($client->consentement_modifie_le);
    }

    public function test_detecte_un_doublon_quel_que_soit_le_format(): void
    {
        $this->actingAs($this->utilisateur());
        $this->post(route('clients.store'), $this->donnees());

        $this->post(route('clients.store'), $this->donnees(['telephone' => '+221 77-123-45-67']))
            ->assertSessionHasErrors(['telephone' => 'Un client existe déjà avec ce numéro de téléphone.']);
    }

    public function test_recherche_par_nom_et_par_telephone(): void
    {
        $this->actingAs($this->utilisateur());
        Client::factory()->create(['prenom' => 'Khady', 'nom' => 'Sow', 'telephone' => '221781112233']);
        Client::factory()->create(['prenom' => 'Binta', 'nom' => 'Fall', 'telephone' => '221709998877']);

        $this->get('/clients?q=khady')->assertSee('Khady Sow')->assertDontSee('Binta Fall');
        $this->get('/clients?q=70 999')->assertSee('Binta Fall')->assertDontSee('Khady Sow');
    }

    public function test_archive_au_lieu_de_supprimer(): void
    {
        $this->actingAs($this->utilisateur());
        $client = $this->client();

        $this->patch(route('clients.archiver', $client))->assertRedirect(route('clients.index'));

        $this->assertSame(StatutClient::Archive, $client->fresh()->statut);
        $this->delete("/clients/{$client->id}")->assertMethodNotAllowed();
    }

    public function test_export_csv_reserve_aux_personnes_autorisees(): void
    {
        $this->client();
        $this->actingAs($this->utilisateur());

        $reponse = $this->get(route('clients.export'))->assertOk();
        $this->assertStringContainsString('Prénom;Nom;Téléphone', $reponse->streamedContent());
    }

    public function test_utiliser_une_recompense_debite_les_points(): void
    {
        $this->actingAs($this->utilisateur());
        $client = $this->client();
        $recompense = Recompense::query()->where('seuil_points', 50)->firstOrFail();

        $this->post(route('fidelite.utiliser', [$client, $recompense]))->assertSessionHasErrors('general');

        $this->post(route('fidelite.corriger', $client), ['points' => 60, 'motif' => 'Reprise ancien carnet']);
        $this->post(route('fidelite.utiliser', [$client, $recompense]))->assertSessionHasNoErrors();

        $this->assertSame(10, $client->soldePoints());
    }

    public function test_le_lien_whatsapp_redirige_vers_wa_me(): void
    {
        $this->actingAs($this->utilisateur('receptionniste'));
        $client = Client::factory()->create(['prenom' => 'Awa', 'telephone' => '221771234567']);

        $this->get(route('whatsapp.client', [$client, 'anniversaire']))
            ->assertRedirectContains('https://wa.me/221771234567?text=Bonjour%20Awa');
    }
}
