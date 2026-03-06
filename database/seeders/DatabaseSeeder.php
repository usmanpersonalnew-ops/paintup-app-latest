<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MasterSurface;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 0. Create Spatie Roles First
        $adminRole      = Role::firstOrCreate(['name' => 'admin']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $customerRole   = Role::firstOrCreate(['name' => 'customer']); // ✅ Added

        // 1. Create Default Settings
        Setting::firstOrCreate(
            ['id' => 1],
            [
                'company_name'   => 'PaintUp',
                'primary_color'  => '#2563eb',
                'secondary_color'=> '#1e293b',
                'invoice_prefix' => 'INV',
            ]
        );

        // 2. Create ADMIN
        $admin = User::updateOrCreate(
            ['email' => 'admin@paintup.in'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password123'),
            ]
        );
        $admin->assignRole($adminRole);

        // 3. Create USMAN (Admin)
        $usman = User::updateOrCreate(
            ['email' => 'usman@paintup.in'],
            [
                'name'     => 'Usman',
                'password' => Hash::make('password123'),
            ]
        );
        $usman->assignRole($adminRole);

        // 4. Create SUPERVISOR
        $supervisor = User::updateOrCreate(
            ['email' => 'rahul@paintup.in'],
            [
                'name'     => 'Rahul Supervisor',
                'password' => Hash::make('password123'),
            ]
        );
        $supervisor->assignRole($supervisorRole);

        // 5. Create CUSTOMERS (Proper Numbering)
        for ($i = 1; $i <= 5; $i++) {
            $customer = User::updateOrCreate(
                ['email' => "customer{$i}@paintup.in"],
                [
                    'name'     => "Customer {$i}",
                    'password' => Hash::make('password123'),
                ]
            );

            $customer->assignRole($customerRole);
        }

        // 6. Create Master Data
        if (MasterSurface::count() == 0) {
            MasterSurface::create([
                'name' => 'Interior Wall',
                'category' => 'INTERIOR',
                'unit_type' => 'AREA'
            ]);

            MasterSurface::create([
                'name' => 'Ceiling',
                'category' => 'INTERIOR',
                'unit_type' => 'AREA'
            ]);
        }
    }
}