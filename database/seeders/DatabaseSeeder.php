<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $this->call(RoleSeeder::class);
       $this->call(AreaPakirSeeder::class);
       $this->call(TarifSeeder::class);
       $this->call(KendaraanSeeder::class);
       $this->call(TransaksiSeeder::class);
       $this->call(LogAktivitasSeeder::class);
    }
}
