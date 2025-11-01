<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrateur système avec tous les droits',
                'permissions' => [
                    'users.view',
                    'users.create',
                    'users.edit',
                    'users.delete',
                    'clients.view',
                    'clients.create',
                    'clients.edit',
                    'clients.delete',
                    'comptes.view',
                    'comptes.create',
                    'comptes.edit',
                    'comptes.delete',
                    'comptes.block',
                    'comptes.unblock',
                    'transactions.view',
                    'transactions.create',
                    'transactions.edit',
                    'transactions.delete',
                    'reports.view',
                    'settings.view',
                    'settings.edit'
                ]
            ],
            [
                'name' => 'client',
                'description' => 'Client avec accès limité à ses propres comptes',
                'permissions' => [
                    'comptes.view.own',
                    'comptes.create.own',
                    'transactions.view.own',
                    'transactions.create.own',
                    'profile.view',
                    'profile.edit'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}