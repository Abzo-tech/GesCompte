<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['epargne', 'cheque', 'courant']),
            'statut' => $this->faker->randomElement(['actif', 'bloque', 'ferme']),
            'client_id' => \App\Models\Client::factory(),
        ];
    }
}
