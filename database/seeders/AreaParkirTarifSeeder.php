<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaParkirTarifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing areas and tariffs
        $areas = DB::table('area_parkirs')->get();
        $tarifs = DB::table('tarifs')->get();

        // Assign tariffs to areas
        foreach ($areas as $area) {
            $mobilTarif = $tarifs->where('jenis_kendaraan', 'Mobil')->first();
            $motorTarif = $tarifs->where('jenis_kendaraan', 'Motor')->first();
            
            if ($mobilTarif) {
                DB::table('area_parkir_tarif')->insert([
                    'area_parkir_id' => $area->id,
                    'tarif_id' => $mobilTarif->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            if ($motorTarif) {
                DB::table('area_parkir_tarif')->insert([
                    'area_parkir_id' => $area->id,
                    'tarif_id' => $motorTarif->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        $this->command->info('Tarif assignments completed successfully');
    }
}
