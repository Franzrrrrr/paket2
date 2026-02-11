<?php

namespace Database\Seeders;

use App\Models\Transaksi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaksi::firstOrCreate(
            ['id' => 1],
            [
                'kendaraan_id' => 1,
                'waktu_masuk' => now()->subHours(5),
                'waktu_keluar' => now()->subHours(2),
                'durasi_jam' => 3,
                'biaya_total' => 30000,
                'status' => 'selesai',
                'user_id' => 2,
                'area_id' => 1,
                'tarif_id' => 2,
            ]
        );

        Transaksi::firstOrCreate(
            ['id' => 2],
            [
                'kendaraan_id' => 2,
                'waktu_masuk' => now()->subHours(4),
                'waktu_keluar' => now()->subHours(1),
                'durasi_jam' => 3,
                'biaya_total' => 15000,
                'status' => 'selesai',
                'user_id' => 2,
                'area_id' => 2,
                'tarif_id' => 1,
            ]
        );

        Transaksi::firstOrCreate(
            ['id' => 3],
            [
                'kendaraan_id' => 3,
                'waktu_masuk' => now()->subHours(6),
                'waktu_keluar' => now()->subHours(3),
                'durasi_jam' => 3,
                'biaya_total' => 30000,
                'status' => 'selesai',
                'user_id' => 2,
                'area_id' => 1,
                'tarif_id' => 2,
            ]
        );

        Transaksi::firstOrCreate(
            ['id' => 4],
            [
                'kendaraan_id' => 4,
                'waktu_masuk' => now()->subHours(2),
                'waktu_keluar' => null,
                'durasi_jam' => 0,
                'biaya_total' => 0,
                'status' => 'aktif',
                'user_id' => 2,
                'area_id' => 3,
                'tarif_id' => 1,
            ]
        );

        Transaksi::firstOrCreate(
            ['id' => 5],
            [
                'kendaraan_id' => 5,
                'waktu_masuk' => now()->subHours(1),
                'waktu_keluar' => null,
                'durasi_jam' => 0,
                'biaya_total' => 0,
                'status' => 'aktif',
                'user_id' => 2,
                'area_id' => 2,
                'tarif_id' => 2,
            ]
        );
    }
}
