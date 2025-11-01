<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call([
            RoleSeeder::class,
        ]);

        // Then seed other data
        $this->call([
            ClientSeeder::class,
            CompteSeeder::class,
            TransactionSeeder::class,
            UserSeeder::class,
            PassportClientSeeder::class,
        ]);

        // Create test users with roles
        $this->createTestUsers();

        // Anciens seeds commentés (pour référence)
        // \App\Models\User::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }

    /**
     * Create test users with roles
     */
    private function createTestUsers(): void
    {
        // Create admin user
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin = \App\Models\User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@gesbanque.com',
                'is_active' => true,
            ]);
            $admin->roles()->attach($adminRole->id);
        }

        // Create client user
        $clientRole = \App\Models\Role::where('name', 'client')->first();
        if ($clientRole) {
            $client = \App\Models\User::factory()->create([
                'name' => 'Client User',
                'email' => 'client@gesbanque.com',
                'is_active' => true,
            ]);
            $client->roles()->attach($clientRole->id);

            // Créer un nouveau client associé à cet utilisateur
            $clientData = \App\Models\Client::factory()->create([
                'id' => $client->id, // Même ID que l'utilisateur
                'nom' => 'Dupont',
                'prenom' => 'Marie',
                'email' => 'marie.dupont@email.com',
                'telephone' => '+221701234567',
                'adresse' => '123 Rue de la Banque, Dakar, Sénégal',
                'statut' => 'actif'
            ]);

            // Créer quelques comptes pour ce client
            $compteCheque = \App\Models\Compte::factory()->create([
                'client_id' => $client->id,
                'type' => 'cheque',
                'devise' => 'FCFA',
                'statut' => 'actif'
            ]);

            $compteEpargne = \App\Models\Compte::factory()->create([
                'client_id' => $client->id,
                'type' => 'epargne',
                'devise' => 'FCFA',
                'statut' => 'actif'
            ]);

            // Créer des transactions pour calculer le solde
            \App\Models\Transaction::factory()->create([
                'compte_id' => $compteCheque->id,
                'type' => 'depot',
                'montant' => 150000,
                'description' => 'Dépôt initial compte chèque',
                'date_transaction' => now(),
            ]);

            \App\Models\Transaction::factory()->create([
                'compte_id' => $compteEpargne->id,
                'type' => 'depot',
                'montant' => 500000,
                'description' => 'Dépôt initial compte épargne',
                'date_transaction' => now(),
            ]);
        }
    }
}
