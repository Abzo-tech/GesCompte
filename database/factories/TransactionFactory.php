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
        $type = fake()->randomElement(['depot', 'retrait', 'virement', 'paiement']);
        $montant = fake()->randomFloat(2, 10, 5000);

        return [
            'type' => $type,
            'montant' => $montant,
            'description' => fake()->sentence(),
            'beneficiaire' => $type === 'virement' ? fake()->name() : null,
            'date_transaction' => fake()->dateTimeBetween('-6 months', 'now'),
            'compte_id' => \App\Models\Compte::inRandomOrder()->first()?->id ?? \App\Models\Compte::factory(),
        ];
    }
}
