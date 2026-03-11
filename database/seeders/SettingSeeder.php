<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default settings if not exists
        Setting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PaintUp',
                'primary_color' => '#2563eb',
                'secondary_color' => '#1e293b',
                'invoice_prefix' => 'INV',
            ]
        );
    }
}