<?php

namespace App\Http\Controllers;

use App\Enums\StatutRendezVous;
use App\Enums\ZoneSalon;
use App\Http\Requests\RendezVousRequest;
use App\Models\Client;
use App\Models\Employe;
use App\Models\Forfait;
use App\Models\RendezVous;
use App\Models\Service;
use App\Services\RendezVousService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class RendezVousController extends Controller
{
    private const VUES = ['jour', 'semaine', 'mois'];

    public function __construct(private readonly RendezVousService $rendezVous) {}

    public function index(Request $request): View
    {
        return $this->calendrier($request, $request->integer('employe') ?: null, 'rendez-vous.index');
    }

    /** Planning du / de la professionnel(le) connecté(e). */
    public function planning(Request $request): View
    {
        $employe = $request->user()->employe;
        abort_unless($employe, 403, 'Aucune fiche employé n\'est liée à ce compte.');

        return $this->calendrier($request, $employe->id, 'planning');
    }

    public function create(Request $request): View
    {
        $rendezVous = new RendezVous([
            'client_id' => $request->integer('client') ?: null,
            'zone' => ZoneSalon::Mixte,
        ]);

        return view('rendez-vous.form', $this->donneesFormulaire($rendezVous, $request->date('date')?->toDateString() ?? today()->toDateString()));
    }

    public function store(RendezVousRequest $request): RedirectResponse
    {
        $rendezVous = $this->rendezVous->creer($request->validated(), $request->user());

        return redirect()->route('rendez-vous.show', $rendezVous)->with('success', 'Rendez-vous enregistré.');
    }

    public function show(RendezVous $rendezVous): View
    {
        $this->authorize('view', $rendezVous);
        $rendezVous->load(['client', 'employe', 'lignes', 'paiements.mode', 'auteur']);

        return view('rendez-vous.show', ['rdv' => $rendezVous, 'statuts' => StatutRendezVous::cases()]);
    }

    public function edit(RendezVous $rendezVous): View
    {
        $rendezVous->load('lignes');

        return view('rendez-vous.form', $this->donneesFormulaire($rendezVous, $rendezVous->debut->toDateString()));
    }

    public function update(RendezVousRequest $request, RendezVous $rendezVous): RedirectResponse
    {
        $this->rendezVous->mettreAJour($rendezVous, $request->validated());

        return redirect()->route('rendez-vous.show', $rendezVous)->with('success', 'Rendez-vous mis à jour.');
    }

    public function changerStatut(Request $request, RendezVous $rendezVous): RedirectResponse
    {
        $this->authorize('changerStatut', $rendezVous);
        $statut = StatutRendezVous::from($request->validate(['statut' => ['required', new Enum(StatutRendezVous::class)]])['statut']);

        $this->rendezVous->changerStatut($rendezVous, $statut);

        return back()->with('success', "Statut mis à jour : {$statut->label()}.");
    }

    private function calendrier(Request $request, ?int $employeId, string $route): View
    {
        $vue = in_array($request->query('vue'), self::VUES, true) ? $request->query('vue') : 'jour';
        $date = CarbonImmutable::parse($request->date('date') ?? today())->locale('fr');

        [$debut, $fin] = match ($vue) {
            'jour' => [$date->startOfDay(), $date->endOfDay()],
            'semaine' => [$date->startOfWeek(), $date->endOfWeek()],
            'mois' => [$date->startOfMonth()->startOfWeek(), $date->endOfMonth()->endOfWeek()],
        };

        $rendezVous = RendezVous::query()
            ->with(['client', 'employe', 'lignes'])
            ->entre($debut, $fin)
            ->when($employeId, fn ($q) => $q->where('employe_id', $employeId))
            ->orderBy('debut')
            ->get();

        return view('rendez-vous.index', [
            'vue' => $vue,
            'date' => $date,
            'debut' => $debut,
            'fin' => $fin,
            'rendezVous' => $rendezVous,
            'parJour' => $rendezVous->groupBy(fn (RendezVous $r) => $r->debut->toDateString()),
            'employes' => Employe::query()->actifs()->orderBy('prenom')->get(),
            'employeId' => $employeId,
            'route' => $route,
        ]);
    }

    private function donneesFormulaire(RendezVous $rendezVous, string $date): array
    {
        return [
            'rdv' => $rendezVous,
            'date' => $date,
            'clients' => Client::query()->nonArchives()->orderBy('prenom')->get(['id', 'prenom', 'nom', 'telephone']),
            'employes' => Employe::query()->actifs()->with('services:id')->orderBy('prenom')->get(),
            'services' => Service::query()->actifs()->with('categorie')->orderBy('nom')->get()->groupBy('categorie.nom'),
            'forfaits' => Forfait::query()->actifs()->with('services')->orderBy('nom')->get(),
            'zones' => ZoneSalon::cases(),
        ];
    }
}
