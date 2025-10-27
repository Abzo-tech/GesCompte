<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupération du client créé
        $client = \App\Models\Client::first();

        if ($client) {
            // Création manuelle de comptes
            \App\Models\Compte::create([
                'numero' => 'CMPT2025001',
                'type' => 'courant',
                'statut' => 'actif',
                'client_id' => $client->id
            ]);

            \App\Models\Compte::create([
                'numero' => 'CMPT2025002',
                'type' => 'epargne',
                'statut' => 'actif',
                'client_id' => $client->id
            ]);
        }
    }
}
