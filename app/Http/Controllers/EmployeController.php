<?php

namespace App\Http\Controllers;

use App\Enums\StatutRendezVous;
use App\Http\Requests\EmployeRequest;
use App\Models\Employe;
use App\Models\Service;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        $employes = Employe::query()
            ->with('disponibilites')
            ->withCount(['rendezVous as prestations_mois' => fn ($q) => $q
                ->where('statut', StatutRendezVous::Termine)
                ->where('debut', '>=', now()->startOfMonth())])
            ->withSum(['rendezVous as montant_mois' => fn ($q) => $q
                ->where('statut', StatutRendezVous::Termine)
                ->where('debut', '>=', now()->startOfMonth())], 'prix_prevu')
            ->orderByDesc('is_active')->orderBy('prenom')
            ->get();

        return view('employes.index', compact('employes'));
    }

    public function create(): View
    {
        return view('employes.form', ['employe' => new Employe(['is_active' => true]), 'services' => $this->services()]);
    }

    public function store(EmployeRequest $request): RedirectResponse
    {
        $employe = DB::transaction(function () use ($request) {
            $employe = Employe::create($request->safe()->except(['services', 'horaires']));
            $this->synchroniser($employe, $request);
            $this->audit->log('employe.cree', $employe);

            return $employe;
        });

        return redirect()->route('employes.index')->with('success', "{$employe->nom_complet} a été ajouté(e) à l'équipe.");
    }

    public function edit(Employe $employe): View
    {
        return view('employes.form', ['employe' => $employe->load(['services', 'disponibilites']), 'services' => $this->services()]);
    }

    public function update(EmployeRequest $request, Employe $employe): RedirectResponse
    {
        DB::transaction(function () use ($request, $employe) {
            $employe->update($request->safe()->except(['services', 'horaires']));
            $this->synchroniser($employe, $request);
            $this->audit->logChangements('employe.modifie', $employe);
        });

        return redirect()->route('employes.index')->with('success', 'Fiche employé mise à jour.');
    }

    private function synchroniser(Employe $employe, EmployeRequest $request): void
    {
        $employe->services()->sync($request->validated('services', []));
        $employe->disponibilites()->delete();
        $employe->disponibilites()->createMany($request->disponibilites());
    }

    private function services()
    {
        return Service::query()->actifs()->with('categorie')->orderBy('nom')->get()->groupBy('categorie.nom');
    }
}
