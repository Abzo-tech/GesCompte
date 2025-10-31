<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un administrateur par défaut
        \App\Models\User::create([
            'id' => '660e8400-e29b-41d4-a716-446655440000',
            'name' => 'Admin User',
            'email' => 'admin@banque.example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true
        ]);

        // Créer des utilisateurs directement avec DB
        for ($i = 1; $i <= 5; $i++) {
            \App\Models\User::create([
                'id' => '660e8400-e29b-41d4-a716-44665544000' . $i,
                'name' => 'Client ' . $i,
                'email' => 'client' . $i . '@example.com',
                'password' => bcrypt('password123'),
                'role' => 'client',
                'is_active' => true
            ]);
        }
    }
}
