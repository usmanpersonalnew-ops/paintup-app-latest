<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@paintup.in'],
            [
                'name' => 'Admin',
                'email' => 'admin@paintup.in',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN',
            ]
        );

        // Supervisor user
        User::updateOrCreate(
            ['email' => 'usman@paintup.in'],
            [
                'name' => 'Supervisor',
                'email' => 'usman@paintup.in',
                'password' => Hash::make('password123'),
                'role' => 'SUPERVISOR',
            ]
        );
    }
}