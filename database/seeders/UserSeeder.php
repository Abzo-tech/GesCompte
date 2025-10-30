<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un administrateur par défaut
        \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@banque.example.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Créer des clients
        \App\Models\User::factory()->count(5)->create([
            'role' => 'client',
            'is_active' => true,
        ]);

        // Créer des utilisateurs réguliers (clients)
        \App\Models\User::factory()->count(10)->create([
            'role' => 'client',
            'is_active' => true,
        ]);

        // Créer quelques utilisateurs inactifs
        \App\Models\User::factory()->count(2)->inactive()->create();

        // Créer des utilisateurs avec différents rôles
        \App\Models\User::factory()->admin()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@banque.example.com',
        ]);

        \App\Models\User::factory()->client()->create([
            'name' => 'Chief Client',
            'email' => 'chief.client@banque.example.com',
        ]);
    }
}
