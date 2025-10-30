<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Passport\Client;

class PassportClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only run if oauth_clients table exists
        if (\Schema::hasTable('oauth_clients')) {
            // Create personal access client for GesBanque
            Client::create([
                'user_id' => null,
                'name' => 'GesBanque Personal Access Client',
                'secret' => 'gesbanque_secret_key_2025',
                'provider' => null,
                'redirect' => 'http://localhost',
                'personal_access_client' => true,
                'password_client' => false,
                'revoked' => false,
            ]);

            // Create password grant client
            Client::create([
                'user_id' => null,
                'name' => 'GesBanque Password Grant Client',
                'secret' => 'gesbanque_password_secret_2025',
                'provider' => 'users',
                'redirect' => 'http://localhost',
                'personal_access_client' => false,
                'password_client' => true,
                'revoked' => false,
            ]);
        }
    }
}
