<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['depot', 'retrait', 'virement', 'paiement']);
        $montant = $this->faker->randomFloat(2, 10, 5000);

        return [
            'type' => $type,
            'montant' => $montant,
            'description' => $this->faker->sentence(),
            'beneficiaire' => $type === 'virement' ? $this->faker->name() : null,
            'date_transaction' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'compte_id' => \App\Models\Compte::inRandomOrder()->first()?->id ?? \App\Models\Compte::factory(),
        ];
    }
}
