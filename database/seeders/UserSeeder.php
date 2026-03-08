<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $adminRole      = Role::firstOrCreate(['name' => 'admin']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $customerRole   = Role::firstOrCreate(['name' => 'customer']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@paintup.in'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'phone' => '9000000001',
                'status' => 'ACTIVE'
            ]
        );
        $admin->syncRoles($adminRole);

        $supervisor = User::updateOrCreate(
            ['email' => 'rahul@paintup.in'],
            [
                'name' => 'Rahul Supervisor',
                'password' => Hash::make('password123'),
                'phone' => '9000000002',
                'status' => 'ACTIVE'
            ]
        );
        $supervisor->syncRoles($supervisorRole);

        for ($i = 1; $i <= 5; $i++) {

            $customer = User::create([
                'name' => "Customer {$i}",
                'email' => null,
                'phone' => '800000000' . $i,
                'password' => Hash::make('password123'),
                'status' => 'ACTIVE'
            ]);

            $customer->assignRole($customerRole);
        }
    }
}