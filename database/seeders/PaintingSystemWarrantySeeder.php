<?php

namespace Database\Seeders;

use App\Models\MasterPaintingSystem;
use Illuminate\Database\Seeder;

class PaintingSystemWarrantySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Premium Systems - 8 Years (96 months)
        $premiumSystems = [
            'Royale Glitz',
            'Royale Luxury',
            'Royale Aspira',
            'Royale Matt',
            'Royale Shyne',
            'Ultima',
            'Ultima Platinum',
            'Premium Gloss',
            'Premium Silk',
        ];

        foreach ($premiumSystems as $systemName) {
            MasterPaintingSystem::where('system_name', 'LIKE', "%{$systemName}%")
                ->orWhere('system_name', $systemName)
                ->update(['warranty_months' => 96]);
        }

        // Standard Systems - 5 Years (60 months)
        $standardSystems = [
            'Apex',
            'Apex Ultima',
            'Apex Weather',
            'Apex Shield',
            'Apex Distemper',
            'Premium Emulsion',
            'Interior Emulsion',
            'Exterior Emulsion',
        ];

        foreach ($standardSystems as $systemName) {
            MasterPaintingSystem::where('system_name', 'LIKE', "%{$systemName}%")
                ->orWhere('system_name', $systemName)
                ->update(['warranty_months' => 60]);
        }

        // Economy Systems - 3 Years (36 months)
        $economySystems = [
            'Apcolite',
            'Apcolite Premium',
            'Tractor',
            'Tractor Emulsion',
            'Nippon',
            'Dulux',
            'Economy Emulsion',
            'Standard Emulsion',
        ];

        foreach ($economySystems as $systemName) {
            MasterPaintingSystem::where('system_name', 'LIKE', "%{$systemName}%")
                ->orWhere('system_name', $systemName)
                ->update(['warranty_months' => 36]);
        }

        // Distemper / Basic - No Warranty (0 months)
        $distemperSystems = [
            'Distemper',
            'White Cement',
            'Lime Wash',
            'Basic',
            'Economy',
            'Budget',
        ];

        foreach ($distemperSystems as $systemName) {
            MasterPaintingSystem::where('system_name', 'LIKE', "%{$systemName}%")
                ->orWhere('system_name', $systemName)
                ->update(['warranty_months' => 0]);
        }

        // Update any remaining systems with 0 warranty
        MasterPaintingSystem::where('warranty_months', 0)->orWhereNull('warranty_months')
            ->update(['warranty_months' => 0]);
    }
}