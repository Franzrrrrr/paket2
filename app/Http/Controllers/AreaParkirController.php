<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AreaParkirController extends Controller
{
    /**
     * Return summary of all parking areas with occupancy info.
     */
    public function index()
    {
        $areas = \App\Models\AreaParkir::all()->map(function ($area) {
            $rate = $area->kapasitas > 0 ? round(100 * $area->terisi / $area->kapasitas, 1) : 0;

            return [
                'id' => $area->id,
                'nama_area' => $area->nama_area,
                'kapasitas' => $area->kapasitas,
                'terisi' => $area->terisi,
                'occupancy_rate' => $rate,
            ];
        });

        return response()->json(['data' => $areas]);
    }

    /**
     * Show a single area detail.
     */
    public function show($id)
    {
        $area = \App\Models\AreaParkir::findOrFail($id);
        $rate = $area->kapasitas > 0 ? round(100 * $area->terisi / $area->kapasitas, 1) : 0;

        return response()->json([
            'id' => $area->id,
            'nama_area' => $area->nama_area,
            'kapasitas' => $area->kapasitas,
            'terisi' => $area->terisi,
            'occupancy_rate' => $rate,
        ]);
    }

    /**
     * Vehicles currently parked in a given area (optionally all areas).
     *
     * @param  Request  $request
     */
    public function parkedVehicles(Request $request)
    {
        $areaId = $request->query('area_id');
        $query = \App\Models\Transaksi::with('kendaraan')
            ->whereNull('waktu_keluar')
            ->orWhere('status', 'parked');

        if ($areaId) {
            $query->where('area_id', $areaId);
        }

        $vehicles = $query->get()->map(function ($trx) {
            return [
                'plat_nomor' => $trx->kendaraan->plat_nomor,
                'waktu_masuk' => $trx->waktu_masuk,
            ];
        });

        return response()->json(['data' => $vehicles]);
    }
}

