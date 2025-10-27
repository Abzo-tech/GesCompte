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
        // Récupération de tous les clients
        $clients = \App\Models\Client::all();

        foreach ($clients as $client) {
            // Création de 2 comptes par client (un courant et un épargne)
            \App\Models\Compte::create([
                'numero' => 'CMPT' . date('Y') . 'C' . strtoupper(substr(md5($client->id . 'courant'), 0, 6)),
                'type' => 'courant',
                'statut' => 'actif',
                'client_id' => $client->id
            ]);

            \App\Models\Compte::create([
                'numero' => 'CMPT' . date('Y') . 'E' . strtoupper(substr(md5($client->id . 'epargne'), 0, 6)),
                'type' => 'epargne',
                'statut' => 'actif',
                'client_id' => $client->id
            ]);
        }

        // Création de comptes spécifiques pour les tests
        $jeanMartin = \App\Models\Client::where('email', 'jean.martin@example.com')->first();
        if ($jeanMartin) {
            // Compte avec solde négatif
            \App\Models\Compte::create([
                'numero' => 'CMPT2025001',
                'type' => 'courant',
                'statut' => 'actif',
                'client_id' => $jeanMartin->id
            ]);

            // Compte avec solde positif
            \App\Models\Compte::create([
                'numero' => 'CMPT2025002',
                'type' => 'epargne',
                'statut' => 'actif',
                'client_id' => $jeanMartin->id
            ]);
        }
    }
}
