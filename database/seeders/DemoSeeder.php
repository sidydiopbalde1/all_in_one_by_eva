<?php

namespace Database\Seeders;

use App\Enums\StatutRendezVous;
use App\Exceptions\RegleMetierException;
use App\Models\CategorieDepense;
use App\Models\Client;
use App\Models\Depense;
use App\Models\Employe;
use App\Models\ModePaiement;
use App\Models\Produit;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Services\PaiementService;
use App\Services\RendezVousService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

/** Données de démonstration — à ne pas lancer en production. */
class DemoSeeder extends Seeder
{
    public function run(RendezVousService $rendezVous, PaiementService $paiements): void
    {
        $admin = $this->utilisateur('Eva Diallo', 'admin@allinone.test', 'admin');
        $this->utilisateur('Awa Ndiaye', 'accueil@allinone.test', 'receptionniste');
        Auth::login($admin);

        $equipe = [
            ['Fatou', 'Sarr', 'Coiffeuse', 'Tresses, coloration', ['Tresses', 'Coloration', 'Coiffure femmes', 'Soins capillaires', 'Coiffure enfants'], 'fatou@allinone.test'],
            ['Moussa', 'Ba', 'Coiffeur barbier', 'Coupes hommes et enfants', ['Coiffure hommes', 'Coiffure enfants'], null],
            ['Aminata', 'Fall', 'Esthéticienne', 'Massages, soins du visage, onglerie', ['Massages', 'Soins du visage', 'Manucure', 'Pédicure'], null],
        ];

        $employes = collect($equipe)->map(function ($e) {
            $user = $e[5] ? $this->utilisateur("{$e[0]} {$e[1]}", $e[5], 'professionnel') : null;
            $employe = Employe::create(['prenom' => $e[0], 'nom' => $e[1], 'telephone' => '77'.random_int(1000000, 9999999), 'fonction' => $e[2], 'specialites' => $e[3], 'taux_commission' => 10, 'user_id' => $user?->id]);
            $employe->services()->sync(Service::query()->whereHas('categorie', fn ($q) => $q->whereIn('nom', $e[4]))->pluck('id'));
            foreach (range(1, 6) as $jour) {
                $employe->disponibilites()->create(['jour_semaine' => $jour, 'heure_debut' => '08:00', 'heure_fin' => '22:00']);
            }

            return $employe->load('services');
        });

        $clients = Client::factory()->count(36)->create();
        Client::factory()->nouveau()->count(4)->create();
        $modes = ModePaiement::query()->pluck('id');

        // Historique : rendez-vous passés terminés et payés, puis rendez-vous à venir.
        foreach (range(1, 140) as $i) {
            $jour = CarbonImmutable::today()->subDays(random_int(-10, 170));
            if ($jour->isSunday()) {
                continue;
            }
            $employe = $employes->random();
            $heure = sprintf('%02d:%s', random_int(9, 18), ['00', '30'][random_int(0, 1)]);

            try {
                $rdv = $rendezVous->creer([
                    'client_id' => $clients->random()->id, 'employe_id' => $employe->id, 'date' => $jour->toDateString(),
                    'heure_debut' => $heure, 'zone' => 'mixte',
                    'services' => $employe->services->where('zone.value', 'mixte')->random(1)->pluck('id')->all(),
                ], $admin);
            } catch (RegleMetierException) {
                continue; // créneau déjà pris : on passe
            }

            if ($jour->isPast() && ! $jour->isToday()) {
                $statut = random_int(1, 12) === 1 ? StatutRendezVous::Annule : StatutRendezVous::Termine;
                if ($statut === StatutRendezVous::Termine && random_int(1, 10) > 1) {
                    $paiements->enregistrer($rdv, ['mode_paiement_id' => $modes->random(), 'montant' => $rdv->prix_prevu, 'paye_le' => $rdv->fin], $admin);
                }
                $rendezVous->changerStatut($rdv, $statut);
            } elseif (random_int(0, 1)) {
                $rendezVous->changerStatut($rdv, StatutRendezVous::Confirme);
            }
        }

        $categories = CategorieDepense::query()->pluck('id', 'nom');
        foreach (range(0, 5) as $mois) {
            $date = now()->startOfMonth()->subMonths($mois);
            Depense::create(['categorie_depense_id' => $categories['Loyer'], 'montant' => 250000, 'date_depense' => $date->copy()->addDays(4), 'description' => 'Loyer du local', 'enregistre_par' => $admin->id]);
            Depense::create(['categorie_depense_id' => $categories['Électricité'], 'montant' => random_int(35, 60) * 1000, 'date_depense' => $date->copy()->addDays(12), 'description' => 'Facture Senelec', 'enregistre_par' => $admin->id]);
        }

        Produit::create(['nom' => 'Shampooing professionnel', 'categorie' => 'Capillaire', 'quantite' => 3, 'unite' => 'flacon', 'prix_achat' => 6500, 'seuil_alerte' => 5]);
        Produit::create(['nom' => 'Huile de massage', 'categorie' => 'Soins', 'quantite' => 12, 'unite' => 'flacon', 'prix_achat' => 4000, 'seuil_alerte' => 4]);
        Produit::create(['nom' => 'Mèches synthétiques', 'categorie' => 'Tresses', 'quantite' => 8, 'unite' => 'paquet', 'prix_achat' => 1500, 'seuil_alerte' => 10]);

        Auth::logout();
    }

    private function utilisateur(string $nom, string $email, string $role): User
    {
        return User::create([
            'name' => $nom, 'email' => $email, 'password' => 'password',
            'role_id' => Role::query()->where('name', $role)->value('id'),
        ]);
    }
}
