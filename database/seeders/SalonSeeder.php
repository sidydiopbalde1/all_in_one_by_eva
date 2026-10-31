<?php

namespace Database\Seeders;

use App\Enums\TypeMessage;
use App\Enums\TypeRecompense;
use App\Models\CategorieDepense;
use App\Models\CategorieService;
use App\Models\Forfait;
use App\Models\ModeleMessage;
use App\Models\ModePaiement;
use App\Models\ParametreSalon;
use App\Models\Recompense;
use App\Models\Service;
use Illuminate\Database\Seeder;

/** Données de référence du salon (indispensables en production). */
class SalonSeeder extends Seeder
{
    private const CATALOGUE = [
        'Coiffure hommes' => [['Coupe homme', 3000, 30], ['Coupe + barbe', 5000, 45]],
        'Coiffure femmes' => [['Brushing', 5000, 45], ['Coupe femme', 7000, 60, 'femmes']],
        'Coiffure enfants' => [['Coupe enfant', 2000, 20]],
        'Tresses' => [['Tresses simples', 10000, 120, 'femmes'], ['Box braids', 25000, 240, 'femmes'], ['Nattes collées', 8000, 90, 'femmes']],
        'Coloration' => [['Coloration complète', 15000, 90, 'femmes'], ['Mèches', 20000, 120, 'femmes']],
        'Soins capillaires' => [['Soin profond', 7000, 45], ['Défrisage', 10000, 60, 'femmes']],
        'Massages' => [['Massage relaxant 60 min', 20000, 60], ['Massage aux pierres chaudes', 30000, 75]],
        'Manucure' => [['Manucure simple', 5000, 30], ['Pose de vernis semi-permanent', 10000, 45, 'femmes']],
        'Pédicure' => [['Pédicure complète', 8000, 45]],
        'Soins du visage' => [['Soin du visage hydratant', 15000, 60], ['Nettoyage de peau', 12000, 45]],
        'Forfaits combinés' => [],
    ];

    private const MODELES = [
        'anniversaire' => ['Message d\'anniversaire', 'Bonjour [Prénom] ! Toute l\'équipe d\'All in One by Eva te souhaite un merveilleux anniversaire. Pour célébrer cette journée spéciale, nous serions ravis de t\'accueillir à nouveau au salon. À très bientôt !'],
        'promotion' => ['Message de promotion', 'Bonjour [Prénom] ! All in One by Eva a une nouvelle offre beauté à te faire découvrir. Contacte-nous pour connaître les détails et réserver ton prochain moment de bien-être.'],
        'rappel_rdv' => ['Rappel de rendez-vous', 'Bonjour [Prénom] ! Nous te rappelons ton rendez-vous chez All in One by Eva prévu le [date] à [heure] pour [prestation]. Nous avons hâte de t\'accueillir.'],
        'fidelisation' => ['Message de fidélisation', 'Bonjour [Prénom] ! Cela fait quelque temps que nous ne t\'avons pas accueilli chez All in One by Eva. Nous serions heureux de te retrouver pour un nouveau moment de détente et de beauté.'],
    ];

    public function run(): void
    {
        $ordre = 0;
        foreach (self::CATALOGUE as $categorie => $services) {
            $cat = CategorieService::updateOrCreate(['nom' => $categorie], ['ordre' => ++$ordre]);
            foreach ($services as $s) {
                Service::updateOrCreate(
                    ['nom' => $s[0]],
                    ['categorie_service_id' => $cat->id, 'prix' => $s[1], 'duree_minutes' => $s[2], 'zone' => $s[3] ?? 'mixte', 'is_active' => true],
                );
            }
        }

        $duo = Forfait::updateOrCreate(['nom' => 'Duo détente'], ['description' => 'Massage relaxant et soin du visage', 'prix' => 30000, 'is_active' => true]);
        $duo->services()->sync(Service::query()->whereIn('nom', ['Massage relaxant 60 min', 'Soin du visage hydratant'])->pluck('id'));
        $mains = Forfait::updateOrCreate(['nom' => 'Mains & pieds'], ['description' => 'Manucure et pédicure', 'prix' => 11000, 'is_active' => true]);
        $mains->services()->sync(Service::query()->whereIn('nom', ['Manucure simple', 'Pédicure complète'])->pluck('id'));

        foreach (['Espèces', 'Wave', 'Orange Money', 'Carte bancaire'] as $mode) {
            ModePaiement::updateOrCreate(['libelle' => $mode], ['is_active' => true]);
        }

        foreach (['Achat de produits', 'Loyer', 'Électricité', 'Eau', 'Salaires et commissions', 'Entretien du matériel', 'Autres dépenses'] as $categorie) {
            CategorieDepense::updateOrCreate(['nom' => $categorie]);
        }

        foreach (self::MODELES as $type => [$titre, $contenu]) {
            ModeleMessage::firstOrCreate(['type' => TypeMessage::from($type)], ['titre' => $titre, 'contenu' => $contenu]);
        }

        Recompense::firstOrCreate(['libelle' => 'Réduction de 2 000 FCFA'], ['type' => TypeRecompense::Reduction, 'seuil_points' => 50, 'valeur' => 2000]);
        Recompense::firstOrCreate(['libelle' => 'Manucure offerte'], ['type' => TypeRecompense::PrestationOfferte, 'seuil_points' => 100]);

        foreach (['pause_minutes' => 0, 'fidelite_fcfa_par_point' => 1000, 'inactivite_semaines' => 6, 'offre_anniversaire' => null] as $cle => $valeur) {
            if (ParametreSalon::query()->where('cle', $cle)->doesntExist()) {
                ParametreSalon::set($cle, $valeur);
            }
        }
    }
}
