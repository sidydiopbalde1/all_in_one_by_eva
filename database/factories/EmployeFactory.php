<?php

namespace Database\Factories;

use App\Models\Employe;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Employe> */
class EmployeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'prenom' => fake()->firstName(),
            'nom' => fake()->lastName(),
            'telephone' => fake()->numerify('77#######'),
            'fonction' => 'Coiffeuse',
            'is_active' => true,
        ];
    }

    /** Disponible du lundi au samedi, 09:00–19:00. */
    public function disponible(string $debut = '09:00', string $fin = '19:00'): static
    {
        return $this->afterCreating(function (Employe $employe) use ($debut, $fin) {
            foreach (range(1, 6) as $jour) {
                $employe->disponibilites()->create(['jour_semaine' => $jour, 'heure_debut' => $debut, 'heure_fin' => $fin]);
            }
        });
    }
}
