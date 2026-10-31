<?php

use App\Enums\Permission as P;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\FideliteController;
use App\Http\Controllers\ForfaitController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [LoginController::class, 'create'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');

    // Planning personnel (professionnel·le) et statut des prestations — contrôlé par RendezVousPolicy.
    Route::get('/planning', [RendezVousController::class, 'planning'])->name('planning');
    Route::get('/rendez-vous/{rendezVous}', [RendezVousController::class, 'show'])->name('rendez-vous.show')->whereNumber('rendezVous');
    Route::patch('/rendez-vous/{rendezVous}/statut', [RendezVousController::class, 'changerStatut'])->name('rendez-vous.statut');

    Route::middleware('can:'.P::ClientsGerer->value)->group(function () {
        Route::get('/clients/export', [ClientController::class, 'export'])->name('clients.export')->middleware('can:'.P::ClientsExporter->value);
        Route::resource('clients', ClientController::class)->except('destroy');
        Route::patch('/clients/{client}/archiver', [ClientController::class, 'archiver'])->name('clients.archiver');
        Route::post('/clients/{client}/recompenses/{recompense}', [FideliteController::class, 'utiliser'])->name('fidelite.utiliser');
        Route::post('/clients/{client}/points', [FideliteController::class, 'corriger'])->name('fidelite.corriger')->middleware('can:'.P::FideliteCorriger->value);
    });

    Route::middleware('can:'.P::WhatsAppEnvoyer->value)->group(function () {
        Route::get('/clients/{client}/whatsapp/{type}', [WhatsAppController::class, 'client'])->name('whatsapp.client');
        Route::get('/rendez-vous/{rendezVous}/rappel', [WhatsAppController::class, 'rappel'])->name('whatsapp.rappel');
    });

    Route::get('/catalogue', CatalogueController::class)->name('catalogue');
    Route::middleware('can:'.P::CatalogueGerer->value)->group(function () {
        Route::resource('services', ServiceController::class)->only(['create', 'store', 'edit', 'update']);
        Route::resource('forfaits', ForfaitController::class)->only(['create', 'store', 'edit', 'update']);
    });

    Route::middleware('can:'.P::RendezVousGerer->value)->group(function () {
        Route::resource('rendez-vous', RendezVousController::class)
            ->parameters(['rendez-vous' => 'rendezVous'])
            ->only(['index', 'create', 'store', 'edit', 'update']);
    });

    Route::middleware('can:'.P::PaiementsEnregistrer->value)->group(function () {
        Route::get('/paiements', [PaiementController::class, 'index'])->name('paiements.index');
        Route::get('/paiements/impayes', [PaiementController::class, 'impayes'])->name('paiements.impayes');
        Route::get('/rendez-vous/{rendezVous}/paiement', [PaiementController::class, 'create'])->name('paiements.create');
        Route::post('/rendez-vous/{rendezVous}/paiement', [PaiementController::class, 'store'])->name('paiements.store');
        Route::get('/paiements/{paiement}/recu', [PaiementController::class, 'recu'])->name('paiements.recu');
        Route::patch('/paiements/{paiement}/annuler', [PaiementController::class, 'annuler'])->name('paiements.annuler')->middleware('can:'.P::PaiementsAnnuler->value);
    });

    Route::middleware('can:'.P::EmployesGerer->value)->group(function () {
        Route::resource('employes', EmployeController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:'.P::ParametresGerer->value)->group(function () {
        Route::get('/parametres', [ParametreController::class, 'edit'])->name('parametres.edit');
        Route::put('/parametres', [ParametreController::class, 'update'])->name('parametres.update');
    });
});
