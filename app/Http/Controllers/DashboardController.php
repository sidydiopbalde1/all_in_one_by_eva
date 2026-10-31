<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Services\StatistiquesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, StatistiquesService $statistiques): View|RedirectResponse
    {
        // Le/la professionnel(le) arrive directement sur son planning.
        if ($request->user()->cannot(Permission::RendezVousGerer->value)) {
            return redirect()->route('planning');
        }

        return view('dashboard', [
            'stats' => $statistiques->tableauDeBord(),
            'evolution' => $statistiques->evolutionChiffreAffaires(),
            'voirFinances' => $request->user()->can(Permission::FinancesVoir->value),
        ]);
    }
}
