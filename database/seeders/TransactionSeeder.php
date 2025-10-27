<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création de transactions pour les comptes existants
        $comptes = \App\Models\Compte::all();

        foreach ($comptes as $compte) {
            // Création de 3 à 8 transactions par compte
            \App\Models\Transaction::factory(rand(3, 8))->create([
                'compte_id' => $compte->id
            ]);
        }
    }
}
