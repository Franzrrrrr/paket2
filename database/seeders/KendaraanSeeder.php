<?php

namespace Database\Seeders;

use App\Models\Kendaraan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KendaraanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kendaraan::firstOrCreate(
            ['plat_nomor' => 'DK 1234 AB'],
            ['jenis_kendaraan' => 'mobil', 'warna' => 'Merah', 'pemilik' => 'Budi Santoso', 'user_id' => 2]
        );

        Kendaraan::firstOrCreate(
            ['plat_nomor' => 'DK 5678 CD'],
            ['jenis_kendaraan' => 'motor', 'warna' => 'Hitam', 'pemilik' => 'Siti Nurhaliza', 'user_id' => 2]
        );

        Kendaraan::firstOrCreate(
            ['plat_nomor' => 'DK 9101 EF'],
            ['jenis_kendaraan' => 'mobil', 'warna' => 'Putih', 'pemilik' => 'Ahmad Wijaya', 'user_id' => 2]
        );

        Kendaraan::firstOrCreate(
            ['plat_nomor' => 'DK 1121 GH'],
            ['jenis_kendaraan' => 'motor', 'warna' => 'Biru', 'pemilik' => 'Rini Kusuma', 'user_id' => 2]
        );

        Kendaraan::firstOrCreate(
            ['plat_nomor' => 'DK 3141 IJ'],
            ['jenis_kendaraan' => 'mobil', 'warna' => 'Silver', 'pemilik' => 'Doni Hartono', 'user_id' => 2]
        );
    }
}
