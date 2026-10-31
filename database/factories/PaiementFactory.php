<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ModePaiement;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Paiement> */
class PaiementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'mode_paiement_id' => fn () => ModePaiement::query()->value('id'),
            'montant_total' => 10000,
            'montant_paye' => 10000,
            'reste_a_payer' => 0,
            'paye_le' => now(),
            'enregistre_par' => User::factory(),
        ];
    }
}
