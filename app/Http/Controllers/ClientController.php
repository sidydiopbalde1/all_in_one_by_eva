<?php

namespace App\Http\Controllers;

use App\Enums\StatutClient;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Models\ParametreSalon;
use App\Models\Recompense;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request): View
    {
        $filtre = $request->string('filtre')->toString();

        $clients = $this->requete($request)
            ->withCount(['rendezVous as visites_count' => fn ($q) => $q->where('statut', 'termine')])
            ->withMax(['rendezVous as derniere_visite' => fn ($q) => $q->where('statut', 'termine')], 'debut')
            ->when($filtre === 'fideles', fn ($q) => $q->orderByDesc('visites_count'))
            ->unless($filtre === 'fideles', fn ($q) => $q->orderBy('nom')->orderBy('prenom'))
            ->paginate(20)
            ->withQueryString();

        return view('clients.index', compact('clients', 'filtre'));
    }

    public function create(): View
    {
        return view('clients.form', ['client' => new Client(['preference_communication' => 'whatsapp'])]);
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $client = Client::create([
            ...$request->validated(),
            'date_inscription' => today(),
            'statut' => StatutClient::Actif,
            'consentement_modifie_le' => $request->boolean('consentement_promo') ? now() : null,
        ]);
        $this->audit->log('client.cree', $client);

        return redirect()->route('clients.show', $client)->with('success', 'Client enregistré.');
    }

    public function show(Client $client): View
    {
        $client->load([
            'rendezVous' => fn ($q) => $q->with(['employe', 'lignes'])->latest('debut'),
            'paiements' => fn ($q) => $q->with('mode')->latest('paye_le'),
            'mouvementsPoints' => fn ($q) => $q->latest('created_at'),
        ]);

        $solde = $client->soldePoints();

        return view('clients.show', [
            'client' => $client,
            'solde' => $solde,
            'totalPaye' => $client->paiements->whereNull('annule_le')->sum('montant_paye'),
            'derniereVisite' => $client->derniereVisite(),
            'recompenses' => Recompense::query()->actives()->orderBy('seuil_points')->get(),
        ]);
    }

    public function edit(Client $client): View
    {
        return view('clients.form', compact('client'));
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $donnees = $request->validated();

        if ($request->boolean('consentement_promo') !== $client->consentement_promo) {
            $donnees['consentement_modifie_le'] = now();
        }

        $client->update($donnees);
        $this->audit->logChangements('client.modifie', $client);

        return redirect()->route('clients.show', $client)->with('success', 'Fiche client mise à jour.');
    }

    /** Pas de suppression physique : le client est archivé pour conserver son historique financier. */
    public function archiver(Client $client): RedirectResponse
    {
        $client->update(['statut' => StatutClient::Archive]);
        $this->audit->logChangements('client.archive', $client);

        return redirect()->route('clients.index')->with('success', "{$client->nom_complet} a été archivé(e).");
    }

    public function export(Request $request): StreamedResponse
    {
        $this->audit->log('client.export');
        $clients = $this->requete($request)->orderBy('nom')->cursor();

        return response()->streamDownload(function () use ($clients) {
            $sortie = fopen('php://output', 'w');
            fwrite($sortie, "\xEF\xBB\xBF"); // BOM pour Excel
            fputcsv($sortie, ['Prénom', 'Nom', 'Téléphone', 'WhatsApp', 'Email', 'Inscription', 'Consentement promo', 'Statut'], ';');

            foreach ($clients as $client) {
                fputcsv($sortie, [
                    $client->prenom, $client->nom, $client->telephone, $client->whatsapp, $client->email,
                    $client->date_inscription->format('d/m/Y'), $client->consentement_promo ? 'Oui' : 'Non',
                    $client->statut->label(),
                ], ';');
            }

            fclose($sortie);
        }, 'clients-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function requete(Request $request)
    {
        $filtre = $request->string('filtre')->toString();

        return Client::query()
            ->recherche($request->string('q')->toString())
            ->when($filtre === 'archives', fn ($q) => $q->where('statut', StatutClient::Archive))
            ->when($filtre !== 'archives', fn ($q) => $q->nonArchives())
            ->when($filtre === 'inactifs', fn ($q) => $q->inactifsDepuis((int) ParametreSalon::get('inactivite_semaines', 6)))
            ->when($filtre === 'promo', fn ($q) => $q->where('consentement_promo', true));
    }
}
