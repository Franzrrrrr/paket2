<?php

namespace Database\Seeders;

use App\Models\LogAktivitas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LogAktivitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LogAktivitas::create([
            'aktivitas' => 'Login sistem',
            'user_id' => 1, // Admin
        ]);

        LogAktivitas::create([
            'aktivitas' => 'Kendaraan DK 1234 AB masuk ke area parkir',
            'user_id' => 2, // Petugas
        ]);

        LogAktivitas::create([
            'aktivitas' => 'Transaksi parkir selesai untuk kendaraan DK 1234 AB',
            'user_id' => 2, // Petugas
        ]);

        LogAktivitas::create([
            'aktivitas' => 'Kendaraan DK 5678 CD masuk ke area parkir',
            'user_id' => 2, // Petugas
        ]);

        LogAktivitas::create([
            'aktivitas' => 'Mengubah data tarif motor',
            'user_id' => 1, // Admin
        ]);

        LogAktivitas::create([
            'aktivitas' => 'Membuat laporan harian parkir',
            'user_id' => 3, // Owner
        ]);

        LogAktivitas::create([
            'aktivitas' => 'Kendaraan DK 9101 EF keluar dari area parkir',
            'user_id' => 2, // Petugas
        ]);

        LogAktivitas::create([
            'aktivitas' => 'Verifikasi pembayaran transaksi parkir',
            'user_id' => 2, // Petugas
        ]);
    }
}
