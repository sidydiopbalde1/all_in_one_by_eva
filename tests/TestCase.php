<?php

namespace Tests;

use App\Models\Client;
use App\Models\Employe;
use App\Models\ModePaiement;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SalonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, SalonSeeder::class]);
        Carbon::setTestNow('2026-11-02 08:00:00'); // un lundi
    }

    protected function utilisateur(string $role = 'admin'): User
    {
        return User::factory()->role($role)->create();
    }

    protected function employe(): Employe
    {
        return Employe::factory()->disponible('09:00', '19:00')->create();
    }

    protected function client(): Client
    {
        return Client::factory()->create();
    }

    protected function service(int $prix = 10000, int $duree = 60): Service
    {
        return Service::factory()->create(['prix' => $prix, 'duree_minutes' => $duree]);
    }

    protected function modePaiement(): ModePaiement
    {
        return ModePaiement::query()->firstOrFail();
    }

    /** Données valides pour créer un rendez-vous. */
    protected function donneesRdv(Employe $employe, Client $client, Service $service, string $date = '2026-11-03', string $heure = '10:00'): array
    {
        return [
            'client_id' => $client->id, 'employe_id' => $employe->id, 'date' => $date,
            'heure_debut' => $heure, 'zone' => 'mixte', 'services' => [$service->id],
        ];
    }
}
