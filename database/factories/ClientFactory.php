<?php

namespace Database\Factories;

use App\Enums\StatutClient;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Client> */
class ClientFactory extends Factory
{
    private const PRENOMS = ['Aïssatou', 'Mariama', 'Khady', 'Ndeye', 'Coumba', 'Astou', 'Ibrahima', 'Cheikh', 'Ousmane', 'Mame Diarra', 'Adama', 'Seynabou', 'Babacar', 'Rokhaya', 'Mamadou', 'Binta'];

    private const NOMS = ['Diop', 'Ndiaye', 'Fall', 'Sow', 'Gueye', 'Faye', 'Mbaye', 'Cissé', 'Diallo', 'Sy', 'Kane', 'Thiam', 'Seck', 'Niang'];

    public function definition(): array
    {
        return [
            'prenom' => fake()->randomElement(self::PRENOMS),
            'nom' => fake()->randomElement(self::NOMS),
            'telephone' => '221'.fake()->unique()->numerify('7########'),
            'email' => fake()->optional(0.3)->safeEmail(),
            'date_naissance' => fake()->optional(0.8)->dateTimeBetween('-60 years', '-15 years'),
            'date_inscription' => fake()->dateTimeBetween('-1 year', '-6 months'),
            'preference_communication' => 'whatsapp',
            'consentement_promo' => fake()->boolean(70),
            'statut' => StatutClient::Actif,
        ];
    }

    public function nouveau(): static
    {
        return $this->state(fn () => ['date_inscription' => fake()->dateTimeBetween('-3 weeks', 'now')]);
    }
}
