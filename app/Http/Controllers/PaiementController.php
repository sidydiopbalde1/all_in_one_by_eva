<?php

namespace App\Http\Controllers;

use App\Enums\StatutRendezVous;
use App\Http\Requests\PaiementRequest;
use App\Models\ModePaiement;
use App\Models\Paiement;
use App\Models\RendezVous;
use App\Services\PaiementService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaiementController extends Controller
{
    public function __construct(private readonly PaiementService $paiements) {}

    public function index(Request $request): View
    {
        $du = CarbonImmutable::parse($request->date('du') ?? now()->startOfMonth())->startOfDay();
        $au = CarbonImmutable::parse($request->date('au') ?? now())->endOfDay();

        $paiements = Paiement::query()
            ->with(['client', 'mode', 'auteur'])
            ->whereBetween('paye_le', [$du, $au])
            ->when($request->integer('mode'), fn ($q, $mode) => $q->where('mode_paiement_id', $mode))
            ->latest('paye_le')
            ->paginate(25)
            ->withQueryString();

        $totaux = Paiement::query()->valides()->whereBetween('paye_le', [$du, $au])
            ->when($request->integer('mode'), fn ($q, $mode) => $q->where('mode_paiement_id', $mode))
            ->selectRaw('mode_paiement_id, sum(montant_paye) as total')
            ->groupBy('mode_paiement_id')
            ->pluck('total', 'mode_paiement_id');

        return view('paiements.index', [
            'paiements' => $paiements,
            'totaux' => $totaux,
            'modes' => ModePaiement::query()->orderBy('libelle')->get(),
            'du' => $du,
            'au' => $au,
        ]);
    }

    /** Rendez-vous terminés ou en cours avec un reste à payer. */
    public function impayes(): View
    {
        $rendezVous = RendezVous::query()
            ->with(['client', 'employe'])
            ->withSum(['paiements as deja_paye' => fn ($q) => $q->valides()], 'montant_paye')
            ->whereIn('statut', [StatutRendezVous::Termine, StatutRendezVous::EnCours, StatutRendezVous::ClientArrive])
            ->latest('debut')
            ->get()
            ->filter(fn (RendezVous $r) => (int) $r->deja_paye < $r->prix_prevu);

        return view('paiements.impayes', compact('rendezVous'));
    }

    public function create(RendezVous $rendezVous): View
    {
        $rendezVous->load(['client', 'lignes', 'paiements' => fn ($q) => $q->with('mode')->latest('paye_le')]);

        return view('paiements.create', [
            'rdv' => $rendezVous,
            'reste' => $rendezVous->resteAPayer(),
            'modes' => ModePaiement::query()->actifs()->orderBy('libelle')->get(),
        ]);
    }

    public function store(PaiementRequest $request, RendezVous $rendezVous): RedirectResponse
    {
        $paiement = $this->paiements->enregistrer($rendezVous, $request->validated(), $request->user());

        return redirect()->route('paiements.recu', $paiement)->with('success', 'Paiement enregistré.');
    }

    public function recu(Paiement $paiement): View
    {
        return view('paiements.recu', ['paiement' => $paiement->load(['client', 'mode', 'auteur', 'rendezVous.lignes', 'rendezVous.employe'])]);
    }

    public function annuler(Request $request, Paiement $paiement): RedirectResponse
    {
        $motif = $request->validate(['motif' => ['required', 'string', 'min:5', 'max:255']])['motif'];
        $this->paiements->annuler($paiement, $motif, $request->user());

        return back()->with('success', 'Paiement annulé. Il reste visible dans l\'historique.');
    }
}
