<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@squashpro.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@squashpro.local',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Club Owner',
                'email' => 'club@squashpro.com',
                'password' => Hash::make('password'),
                'role' => 'club',
            ],
            [
                'name' => 'Player User',
                'email' => 'player@squashpro.com',
                'password' => Hash::make('password'),
                'role' => 'player',
            ],
        ];

        foreach ($users as $payload) {
            $user = User::updateOrCreate(
                ['email' => $payload['email']],
                [
                    'name' => $payload['name'],
                    'password' => $payload['password'],
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$payload['role']]);
        }
    }
}
