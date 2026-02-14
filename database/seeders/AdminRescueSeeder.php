<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminRescueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@paintup.in'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN'
            ]
        );
    }
}
