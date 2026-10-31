<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForfaitRequest;
use App\Models\Forfait;
use App\Models\Service;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ForfaitController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function create(): View
    {
        return view('catalogue.forfait-form', ['forfait' => new Forfait(['is_active' => true]), 'services' => $this->services()]);
    }

    public function store(ForfaitRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $forfait = Forfait::create($request->safe()->except('services'));
            $forfait->services()->sync($request->validated('services'));
            $this->audit->log('forfait.cree', $forfait);
        });

        return redirect()->route('catalogue')->with('success', 'Forfait créé.');
    }

    public function edit(Forfait $forfait): View
    {
        return view('catalogue.forfait-form', ['forfait' => $forfait->load('services'), 'services' => $this->services()]);
    }

    public function update(ForfaitRequest $request, Forfait $forfait): RedirectResponse
    {
        DB::transaction(function () use ($request, $forfait) {
            $forfait->update($request->safe()->except('services'));
            $forfait->services()->sync($request->validated('services'));
            $this->audit->logChangements('forfait.modifie', $forfait);
        });

        return redirect()->route('catalogue')->with('success', 'Forfait mis à jour.');
    }

    private function services()
    {
        return Service::query()->actifs()->with('categorie')->orderBy('nom')->get();
    }
}
