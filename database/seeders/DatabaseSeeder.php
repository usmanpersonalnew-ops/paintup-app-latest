<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MasterSurface;
use App\Models\MasterProduct;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; // <--- ADD THIS

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 0. Create Spatie Roles First
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);

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

        // 1. Create ADMIN
        $admin = User::updateOrCreate(
            ['email' => 'admin@paintup.in'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN'
            ]
        );
        $admin->assignRole($adminRole); // <--- ASSIGN SPATIE ROLE

        // 2. Create USMAN
        $usman = User::updateOrCreate(
            ['email' => 'usman@paintup.in'],
            [
                'name' => 'Usman',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN' 
            ]
        );
        $usman->assignRole($adminRole); // <--- ASSIGN SPATIE ROLE

        // 3. Create SUPERVISOR
        $supervisor = User::updateOrCreate(
            ['email' => 'rahul@paintup.in'],
            [
                'name' => 'Rahul Supervisor',
                'password' => Hash::make('password123'),
                'role' => 'SUPERVISOR'
            ]
        );
        $supervisor->assignRole($supervisorRole); // <--- ASSIGN SPATIE ROLE

        // 4. Create Master Data
        if(MasterSurface::count() == 0) {
            MasterSurface::create(['name' => 'Interior Wall', 'category' => 'INTERIOR', 'unit_type' => 'AREA']);
            MasterSurface::create(['name' => 'Ceiling', 'category' => 'INTERIOR', 'unit_type' => 'AREA']);
        }
    }
}