<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterSurface;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
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