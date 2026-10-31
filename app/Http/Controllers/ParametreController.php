<?php

namespace App\Http\Controllers;

use App\Enums\TypeMessage;
use App\Http\Requests\ParametresRequest;
use App\Models\ModeleMessage;
use App\Models\ParametreSalon;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ParametreController extends Controller
{
    private const CLES = ['pause_minutes', 'fidelite_fcfa_par_point', 'inactivite_semaines', 'offre_anniversaire'];

    public function edit(): View
    {
        return view('parametres.edit', [
            'parametres' => collect(self::CLES)->mapWithKeys(fn ($cle) => [$cle => ParametreSalon::get($cle)]),
            'modeles' => ModeleMessage::query()->get()->keyBy(fn ($m) => $m->type->value),
            'types' => TypeMessage::cases(),
        ]);
    }

    public function update(ParametresRequest $request, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            foreach (self::CLES as $cle) {
                ParametreSalon::set($cle, $request->validated($cle));
            }

            foreach ($request->validated('modeles') as $type => $contenu) {
                ModeleMessage::query()->where('type', $type)->update(['contenu' => $contenu]);
            }
        });

        $audit->log('parametres.modifies');

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
