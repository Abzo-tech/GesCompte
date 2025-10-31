<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création de clients variés pour les tests
        // \App\Models\Client::factory(10)->create();

        // Création d'un client spécifique pour les tests
        \App\Models\Client::create([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'nom' => 'Martin',
            'prenom' => 'Jean',
            'email' => 'jean.martin@example.com',
            'telephone' => '01 23 45 67 89',
            'adresse' => '123 Avenue de la République, 75001 Paris',
            'statut' => 'actif'
        ]);

        // Création d'un client inactif
        \App\Models\Client::create([
            'id' => '550e8400-e29b-41d4-a716-446655440001',
            'nom' => 'Dubois',
            'prenom' => 'Marie',
            'email' => 'marie.dubois@example.com',
            'telephone' => '01 98 76 54 32',
            'adresse' => '456 Rue de la Paix, 69000 Lyon',
            'statut' => 'inactif'
        ]);

        // Création d'un client suspendu
        \App\Models\Client::create([
            'id' => '550e8400-e29b-41d4-a716-446655440002',
            'nom' => 'Garcia',
            'prenom' => 'Carlos',
            'email' => 'carlos.garcia@example.com',
            'telephone' => '04 12 34 56 78',
            'adresse' => '789 Boulevard des Capucines, 13000 Marseille',
            'statut' => 'suspendu'
        ]);
    }
}
