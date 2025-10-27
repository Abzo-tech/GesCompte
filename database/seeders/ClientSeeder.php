<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création d'un client unique pour les tests
        \App\Models\Client::create([
            'nom' => 'Test',
            'prenom' => 'Client',
            'email' => 'test@example.com',
            'telephone' => '0123456789',
            'adresse' => '123 Test Street',
            'statut' => 'actif'
        ]);
    }
}
