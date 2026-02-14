<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Flat discount coupon - ₹500 off
        Coupon::create([
            'code' => 'PAINTUP500',
            'type' => 'FLAT',
            'value' => 500,
            'min_order_amount' => 5000,
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
        ]);

        // Flat discount coupon - ₹1000 off
        Coupon::create([
            'code' => 'PAINTUP1000',
            'type' => 'FLAT',
            'value' => 1000,
            'min_order_amount' => 10000,
            'expires_at' => now()->addMonths(6),
            'is_active' => true,
        ]);

        // Percent discount coupon - 10% off
        Coupon::create([
            'code' => 'SAVE10',
            'type' => 'PERCENT',
            'value' => 10,
            'min_order_amount' => null, // No minimum
            'expires_at' => now()->addMonths(1),
            'is_active' => true,
        ]);

        // Percent discount coupon - 15% off
        Coupon::create([
            'code' => 'SAVE15',
            'type' => 'PERCENT',
            'value' => 15,
            'min_order_amount' => 15000,
            'expires_at' => now()->addMonths(2),
            'is_active' => true,
        ]);

        // Special welcome coupon - ₹2000 off
        Coupon::create([
            'code' => 'WELCOME2000',
            'type' => 'FLAT',
            'value' => 2000,
            'min_order_amount' => 20000,
            'expires_at' => null, // No expiry
            'is_active' => true,
        ]);

        // Inactive coupon for testing
        Coupon::create([
            'code' => 'EXPIRED50',
            'type' => 'FLAT',
            'value' => 50,
            'min_order_amount' => null,
            'expires_at' => now()->subDays(1), // Already expired
            'is_active' => true,
        ]);

        // Disabled coupon for testing
        Coupon::create([
            'code' => 'DISABLED100',
            'type' => 'FLAT',
            'value' => 100,
            'min_order_amount' => null,
            'expires_at' => null,
            'is_active' => false,
        ]);
    }
}