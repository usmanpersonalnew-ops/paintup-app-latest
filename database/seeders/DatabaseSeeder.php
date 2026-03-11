<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MasterSurface;
use App\Models\MasterProduct;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 0. Create Default Settings
        Setting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PaintUp',
                'primary_color' => '#2563eb',
                'secondary_color' => '#1e293b',
                'invoice_prefix' => 'INV',
            ]
        );

        // 1. Create ADMIN (The fallback)
        User::updateOrCreate(
            ['email' => 'admin@paintup.in'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN'
            ]
        );

        // 2. Create USMAN (Your Account - PERMANENT)
        User::updateOrCreate(
            ['email' => 'usman@paintup.in'],
            [
                'name' => 'Usman',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN' // Giving you Admin powers
            ]
        );

        // 3. Create SUPERVISOR
        User::updateOrCreate(
            ['email' => 'rahul@paintup.in'],
            [
                'name' => 'Rahul Supervisor',
                'password' => Hash::make('password123'),
                'role' => 'SUPERVISOR'
            ]
        );

        
        // 4. Create Master Data (So the app isn't empty)
        if(MasterSurface::count() == 0) {
            MasterSurface::create(['name' => 'Interior Wall', 'category' => 'INTERIOR', 'unit_type' => 'AREA']);
            MasterSurface::create(['name' => 'Ceiling', 'category' => 'INTERIOR', 'unit_type' => 'AREA']);
        }
    }
}
