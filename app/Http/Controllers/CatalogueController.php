<?php

namespace App\Http\Controllers;

use App\Models\CategorieService;
use App\Models\Forfait;
use Illuminate\View\View;

class CatalogueController extends Controller
{
    public function __invoke(): View
    {
        return view('catalogue.index', [
            'categories' => CategorieService::query()->with(['services' => fn ($q) => $q->orderBy('nom')])->orderBy('ordre')->get(),
            'forfaits' => Forfait::query()->with('services')->orderBy('nom')->get(),
        ]);
    }
}
