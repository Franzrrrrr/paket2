<?php

namespace App\Http\Controllers;

use App\Models\AreaParkir;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AreaParkirController extends Controller
{
    /**
     * Return summary of all parking areas with occupancy info.
     */
    public function index(): JsonResponse
    {
        $areas = AreaParkir::all()->map(fn($area) => $this->formatArea($area));

        return response()->json(['data' => $areas]);
    }

    /**
     * Show a single area detail.
     */
    public function show(int $id): JsonResponse
    {
        $area = AreaParkir::findOrFail($id);

        return response()->json($this->formatArea($area));
    }

    /**
     * Vehicles currently parked — opsional filter by area_id.
     */
    public function parkedVehicles(Request $request): JsonResponse
    {
        $areaId = $request->query('area_id');

        $query = Transaksi::with('kendaraan')
            ->where(function ($q) {
                // Kendaraan masih parkir: belum keluar ATAU status masih 'masuk'
                $q->whereNull('waktu_keluar')
                  ->orWhere('status', 'masuk');
            });

        if ($areaId) {
            $query->where('id_area', $areaId);
        }

        $vehicles = $query->get()->map(function ($trx) {
            $durasi = $trx->waktu_masuk
                ? Carbon::parse($trx->waktu_masuk)->diffForHumans(Carbon::now(), true)
                : null;

            return [
                'id'          => $trx->id,
                'plat_nomor'  => $trx->kendaraan->plat_nomor ?? '-',
                'jenis'       => $trx->kendaraan->jenis_kendaraan ?? '-',
                'waktu_masuk' => $trx->waktu_masuk?->format('Y-m-d H:i:s'),
                'durasi'      => $durasi,
                'area_id'     => $trx->id_area,
            ];
        });

        return response()->json([
            'total'  => $vehicles->count(),
            'data'   => $vehicles,
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function formatArea(AreaParkir $area): array
    {
        $rate = $area->kapasitas > 0
            ? round(100 * $area->terisi / $area->kapasitas, 1)
            : 0;

        $status = match (true) {
            $rate >= 100 => 'Penuh',
            $rate >= 80  => 'Hampir Penuh',
            default      => 'Tersedia',
        };

        return [
            'id'             => $area->id,
            'nama_area'      => $area->nama_area,
            'alamat'         => $area->alamat,
            'latitude'       => $area->latitude  ? (float) $area->latitude  : null,
            'longitude'      => $area->longitude ? (float) $area->longitude : null,
            'kapasitas'      => $area->kapasitas,
            'terisi'         => $area->terisi,
            'sisa'           => $area->kapasitas - $area->terisi,
            'occupancy_rate' => $rate,
            'status'         => $status,
        ];
    }
}
