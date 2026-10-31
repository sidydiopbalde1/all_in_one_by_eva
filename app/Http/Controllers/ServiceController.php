<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Models\CategorieService;
use App\Models\Service;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function create(): View
    {
        return view('catalogue.service-form', ['service' => new Service(['is_active' => true]), 'categories' => $this->categories()]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $service = Service::create($request->validated());
        $this->audit->log('service.cree', $service);

        return redirect()->route('catalogue')->with('success', 'Prestation ajoutée.');
    }

    public function edit(Service $service): View
    {
        return view('catalogue.service-form', ['service' => $service, 'categories' => $this->categories()]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());
        $this->audit->logChangements('service.modifie', $service);

        return redirect()->route('catalogue')->with('success', 'Prestation mise à jour.');
    }

    private function categories()
    {
        return CategorieService::query()->orderBy('ordre')->get();
    }
}
