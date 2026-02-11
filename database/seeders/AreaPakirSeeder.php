<?php

namespace Database\Seeders;

use App\Models\AreaParkir;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AreaPakirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AreaParkir::firstOrCreate(
            ['nama_area' => 'Area A - Lantai 1'],
            ['kapasitas' => 50, 'terisi' => 30]
        );

        AreaParkir::firstOrCreate(
            ['nama_area' => 'Area B - Lantai 2'],
            ['kapasitas' => 40, 'terisi' => 25]
        );

        AreaParkir::firstOrCreate(
            ['nama_area' => 'Area C - Lantai 3'],
            ['kapasitas' => 60, 'terisi' => 45]
        );

        AreaParkir::firstOrCreate(
            ['nama_area' => 'Area D - Basement'],
            ['kapasitas' => 80, 'terisi' => 60]
        );
    }
}
