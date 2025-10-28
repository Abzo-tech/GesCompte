<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Compte;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupération du premier client
        $client = Client::first();

        if ($client) {
            // Création de quelques comptes de test
            Compte::create([
                'numero' => 'CMPT2025001',
                'type' => 'courant',
                'statut' => 'actif',
                'client_id' => $client->id,
                'devise' => 'FCFA',
                'date_creation' => now()
            ]);

            Compte::create([
                'numero' => 'CMPT2025002',
                'type' => 'epargne',
                'statut' => 'actif',
                'client_id' => $client->id,
                'devise' => 'FCFA',
                'date_creation' => now()
            ]);
        }
    }
}
