<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Recompense;
use App\Services\FideliteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FideliteController extends Controller
{
    public function __construct(private readonly FideliteService $fidelite) {}

    public function utiliser(Request $request, Client $client, Recompense $recompense): RedirectResponse
    {
        $this->fidelite->utiliserRecompense($client, $recompense, $request->user());

        return back()->with('success', "Récompense « {$recompense->libelle} » utilisée.");
    }

    public function corriger(Request $request, Client $client): RedirectResponse
    {
        $donnees = $request->validate([
            'points' => ['required', 'integer', 'not_in:0'],
            'motif' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $this->fidelite->corriger($client, (int) $donnees['points'], $donnees['motif'], $request->user());

        return back()->with('success', 'Correction de points enregistrée.');
    }
}
