<?php

namespace App\Services;

use App\Enums\StatutRendezVous;
use App\Models\Client;
use App\Models\Depense;
use App\Models\LigneRendezVous;
use App\Models\Paiement;
use App\Models\ParametreSalon;
use App\Models\Produit;
use App\Models\RendezVous;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Indicateurs calculés exclusivement à partir des opérations enregistrées.
 * Les paiements annulés sont exclus pour éviter tout double comptage.
 */
class StatistiquesService
{
    public function chiffreAffaires(CarbonInterface $du, CarbonInterface $au): int
    {
        return (int) Paiement::query()->valides()->whereBetween('paye_le', [$du, $au])->sum('montant_paye');
    }

    public function depenses(CarbonInterface $du, CarbonInterface $au): int
    {
        return (int) Depense::query()->whereBetween('date_depense', [$du->toDateString(), $au->toDateString()])->sum('montant');
    }

    /** Indicateur de gestion (et non bénéfice comptable) : recettes encaissées − dépenses enregistrées. */
    public function resultatEstime(CarbonInterface $du, CarbonInterface $au): int
    {
        return $this->chiffreAffaires($du, $au) - $this->depenses($du, $au);
    }

    /** @return array<string, mixed> */
    public function tableauDeBord(): array
    {
        $maintenant = CarbonImmutable::now();
        [$jour, $finJour] = [$maintenant->startOfDay(), $maintenant->endOfDay()];
        [$mois, $finMois] = [$maintenant->startOfMonth(), $maintenant->endOfMonth()];

        return [
            'clients_total' => Client::query()->nonArchives()->count(),
            'clients_nouveaux_mois' => Client::query()->whereBetween('date_inscription', [$mois->toDateString(), $finMois->toDateString()])->count(),
            'rdv_du_jour' => RendezVous::query()->entre($jour, $finJour)->bloquants()->count(),
            'rdv_a_venir' => RendezVous::query()->where('debut', '>', $maintenant)->bloquants()
                ->where('statut', '!=', StatutRendezVous::Termine)->count(),
            'prestations_terminees_mois' => RendezVous::query()->entre($mois, $finMois)->where('statut', StatutRendezVous::Termine)->count(),
            'ca_jour' => $this->chiffreAffaires($jour, $finJour),
            'ca_mois' => $this->chiffreAffaires($mois, $finMois),
            'depenses_mois' => $this->depenses($mois, $finMois),
            'resultat_mois' => $this->resultatEstime($mois, $finMois),
            'anniversaires' => $this->anniversairesProchains(7),
            'clients_a_relancer' => Client::query()->inactifsDepuis($this->semainesInactivite())->count(),
            'semaines_inactivite' => $this->semainesInactivite(),
            'services_populaires' => $this->servicesPopulaires(5),
            'alertes_stock' => Produit::query()->enAlerte()->orderBy('quantite')->limit(5)->get(),
            'rdv_aujourdhui' => RendezVous::query()->with(['client', 'employe', 'lignes'])
                ->entre($jour, $finJour)->orderBy('debut')->get(),
        ];
    }

    public function semainesInactivite(): int
    {
        return (int) ParametreSalon::get('inactivite_semaines', 6);
    }

    /** Clients dont l'anniversaire tombe dans les N prochains jours (année de naissance ignorée). */
    public function anniversairesProchains(int $jours): Collection
    {
        $aujourdhui = CarbonImmutable::today();

        return Client::query()->nonArchives()->whereNotNull('date_naissance')->get()
            ->map(function (Client $client) use ($aujourdhui) {
                $prochain = $client->date_naissance->setYear($aujourdhui->year);
                if ($prochain->lt($aujourdhui)) {
                    $prochain = $prochain->addYear();
                }
                $client->setAttribute('prochain_anniversaire', $prochain);

                return $client;
            })
            ->filter(fn (Client $c) => $c->prochain_anniversaire->diffInDays($aujourdhui, true) < $jours)
            ->sortBy('prochain_anniversaire')
            ->values();
    }

    /** @return Collection<int, object{libelle:string, total:int}> */
    public function servicesPopulaires(int $limite, ?CarbonInterface $depuis = null): Collection
    {
        return LigneRendezVous::query()
            ->selectRaw('libelle, count(*) as total')
            ->whereHas('rendezVous', fn ($q) => $q
                ->where('statut', StatutRendezVous::Termine)
                ->when($depuis, fn ($q) => $q->where('debut', '>=', $depuis)))
            ->groupBy('libelle')
            ->orderByDesc('total')
            ->limit($limite)
            ->get();
    }

    /** Recettes encaissées des 6 derniers mois (pour le graphique). @return array{labels: list<string>, valeurs: list<int>} */
    public function evolutionChiffreAffaires(int $mois = 6): array
    {
        $labels = $valeurs = [];
        for ($i = $mois - 1; $i >= 0; $i--) {
            $debut = CarbonImmutable::now()->startOfMonth()->subMonths($i);
            $labels[] = ucfirst($debut->locale('fr')->translatedFormat('M Y'));
            $valeurs[] = $this->chiffreAffaires($debut, $debut->endOfMonth());
        }

        return compact('labels', 'valeurs');
    }
}
