<?php

namespace Database\Factories;

use App\Models\CategorieService;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'categorie_service_id' => fn () => CategorieService::query()->firstOrCreate(['nom' => 'Tests'])->id,
            'nom' => fake()->unique()->words(2, true),
            'prix' => 10000,
            'duree_minutes' => 60,
            'zone' => 'mixte',
            'is_active' => true,
        ];
    }
}
