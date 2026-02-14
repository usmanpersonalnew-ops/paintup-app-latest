<?php

namespace Database\Seeders;

use App\Models\MasterProduct;
use App\Models\MasterService;
use App\Models\MasterSurface;
use App\Models\ProductSurfaceLink;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Master Surfaces
        $surfaces = [
            ['name' => 'Interior Wall', 'unit_type' => 'AREA', 'category' => 'INTERIOR'],
            ['name' => 'Ceiling', 'unit_type' => 'AREA', 'category' => 'INTERIOR'],
            ['name' => 'MS Railing', 'unit_type' => 'LINEAR', 'category' => 'BOTH'],
            ['name' => 'Wooden Door', 'unit_type' => 'COUNT', 'category' => 'INTERIOR'],
            ['name' => 'Main Gate', 'unit_type' => 'LUMPSUM', 'category' => 'EXTERIOR'],
        ];

        foreach ($surfaces as $surface) {
            MasterSurface::create($surface);
        }

        // 2. Create Master Products
        $products = [
            ['name' => 'Royale Glitz', 'brand' => 'Asian Paints', 'tier' => 'LUXURY'],
            ['name' => 'Apcolite Premium Enamel', 'brand' => 'Asian Paints', 'tier' => 'PREMIUM'],
        ];

        foreach ($products as $product) {
            MasterProduct::create($product);
        }

        // 3. Create Product-Surface Links
        $royaleGlitz = MasterProduct::where('name', 'Royale Glitz')->first();
        $apcolite = MasterProduct::where('name', 'Apcolite Premium Enamel')->first();

        $interiorWall = MasterSurface::where('name', 'Interior Wall')->first();
        $ceiling = MasterSurface::where('name', 'Ceiling')->first();
        $msRailing = MasterSurface::where('name', 'MS Railing')->first();
        $woodenDoor = MasterSurface::where('name', 'Wooden Door')->first();
        $mainGate = MasterSurface::where('name', 'Main Gate')->first();

        // Link Royale Glitz to Interior Wall and Ceiling
        ProductSurfaceLink::create([
            'product_id' => $royaleGlitz->id,
            'surface_id' => $interiorWall->id,
        ]);
        ProductSurfaceLink::create([
            'product_id' => $royaleGlitz->id,
            'surface_id' => $ceiling->id,
        ]);

        // Link Apcolite Premium Enamel to MS Railing, Wooden Door, and Main Gate
        ProductSurfaceLink::create([
            'product_id' => $apcolite->id,
            'surface_id' => $msRailing->id,
        ]);
        ProductSurfaceLink::create([
            'product_id' => $apcolite->id,
            'surface_id' => $woodenDoor->id,
        ]);
        ProductSurfaceLink::create([
            'product_id' => $apcolite->id,
            'surface_id' => $mainGate->id,
        ]);

        // 4. Create Master Services
        $services = [
            ['name' => 'Floor Masking', 'unit_type' => 'AREA', 'default_rate' => 4.00, 'is_repair' => false],
            ['name' => 'Bamboo Scaffolding', 'unit_type' => 'AREA', 'default_rate' => 12.00, 'is_repair' => false],
            ['name' => 'Crack Filling', 'unit_type' => 'LINEAR', 'default_rate' => 15.00, 'is_repair' => true],
            ['name' => 'Furniture Shifting', 'unit_type' => 'COUNT', 'default_rate' => 500.00, 'is_repair' => false],
        ];

        foreach ($services as $service) {
            MasterService::create($service);
        }
    }
}