<?php

namespace App\Http\Controllers;

use App\Models\AreaParkir;
use App\Models\ParkingSession;
use App\Models\Kendaraan;
use App\Models\Transaksi;
use App\Models\LogAktivitas;
use App\Services\TarifCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function book(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_type' => 'required_without:vehicle_id|string|in:Mobil,Motor',
            'vehicle_id' => 'required_without:vehicle_type|integer|exists:kendaraans,id',
            'parking_area_id' => 'required|integer|exists:area_parkirs,id',
            'estimated_duration' => 'nullable|integer|min:1'
        ]);

        $user = $request->user();
        $parkingArea = AreaParkir::findOrFail($request->parking_area_id);

        if ($parkingArea->terisi >= $parkingArea->kapasitas) {
            return response()->json(['message' => 'Area parkir sudah penuh'], 400);
        }

        $vehicle = null;
        if ($request->has('vehicle_id')) {
            $vehicle = Kendaraan::where('id', $request->vehicle_id)
                ->where('user_id', $user->id)
                ->first();
        } elseif ($request->has('vehicle_type')) {
            $vehicle = Kendaraan::create([
                'plat_nomor' => 'TEMP-' . strtoupper(uniqid()),
                'jenis_kendaraan' => $request->vehicle_type,
                'user_id' => $user->id,
            ]);
        }

        $tarif = $parkingArea->tarifs()
            ->where('jenis_kendaraan', $vehicle->jenis_kendaraan)
            ->first();

        if (!$tarif) {
            return response()->json([
                'message' => 'Tarif tidak ditemukan untuk jenis kendaraan ini'
            ], 400);
        }

        if (!$vehicle) {
            return response()->json(['message' => 'Kendaraan tidak valid'], 400);
        }

        $activeSession = ParkingSession::where('vehicle_id', $vehicle->id)
            ->where('status', 'active')
            ->first();

        if ($activeSession) {
            return response()->json(['message' => 'Vehicle already has active parking session'], 422);
        }

        do {
            $ticketCode = 'PK-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        } while (ParkingSession::where('ticket_code', $ticketCode)->exists());

        $parkingSession = ParkingSession::create([
            'ticket_code' => $ticketCode,
            'vehicle_id' => $vehicle->id,
            'parking_area_id' => $parkingArea->id,
            'entry_time' => now(),
            'status' => 'active'
        ]);

        $parkingArea->increment('terisi');

        $transaksi = Transaksi::create([
            'user_id' => $user->id,
            'kendaraan_id' => $vehicle->id,
            'area_id' => $parkingArea->id,
            'tarif_id' => $tarif->id,
            'waktu_masuk' => now(),
            'status' => 'masuk',
            'ticket_code' => $ticketCode,
        ]);

        LogAktivitas::create([
            'user_id' => $user->id,
            'aktivitas' => "User booked parking at {$parkingArea->nama_area} with ticket {$ticketCode} ({$vehicle->jenis_kendaraan})"
        ]);

        return response()->json([
            'message' => 'Booking successful',
            'data' => [
                'ticket_code' => $ticketCode,
                'parking_session' => $parkingSession->load(['vehicle', 'parkingArea']),
                'entry_time' => $parkingSession->entry_time
            ]
        ]);
    }

    public function exit(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_code' => 'required|string'
        ]);

        $user = $request->user();
        $ticketCode = $request->ticket_code;


        $parkingSession = ParkingSession::where('ticket_code', $ticketCode)
            ->where('status', 'active')
            ->first();


        if (!$parkingSession) {
            return response()->json(['message' => 'Ticket tidak valid atau sudah keluar'], 404);
        }

        if ($parkingSession->vehicle->user_id !== $user->id && !$user->hasRole(['admin', 'owner'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $exitTime = now();
        $entryTime = $parkingSession->entry_time;
        $duration = $entryTime->diffInMinutes($exitTime);

        try {
            $feeCalculation = TarifCalculator::calculateFee(
                $parkingSession->vehicle->jenis_kendaraan,
                $entryTime,
                $exitTime,
                $parkingSession->parking_area_id
            );
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        $parkingSession->update([
            'exit_time' => $exitTime,
            'duration' => $duration,
            'status' => 'completed'
        ]);

        \Log::info('Parking Session updated: ' . json_encode($parkingSession));

        $parkingSession->parkingArea->decrement('terisi');

        $transaksi = Transaksi::where('ticket_code', $ticketCode)->first();
        if ($transaksi) {
            $transaksi->update([
                'waktu_keluar' => $exitTime,
                'status' => 'keluar',
                'total_harga' => $feeCalculation['total_fee']
            ]);
        }

        LogAktivitas::create([
            'user_id' => $user->id,
            'aktivitas' => "Vehicle exited from {$parkingSession->parkingArea->nama_area} with ticket {$ticketCode}. Duration: {$duration} minutes, Total: Rp {$feeCalculation['total_fee']}"
        ]);

        return response()->json([
            'message' => 'Exit successful',
            'data' => [
                'ticket_code' => $parkingSession->ticket_code,
                'duration_minutes' => $duration,
                'duration_hours' => $feeCalculation['duration_hours'],
                'fee_breakdown' => $feeCalculation,
                'total_price' => $feeCalculation['total_fee'],
                'exit_time' => $exitTime
            ]
        ]);
    }

    public function userSessions(Request $request): JsonResponse
    {
        $user = $request->user();

        $sessions = ParkingSession::with(['vehicle', 'parkingArea'])
            ->whereHas('vehicle', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($sessions);
    }

    public function activeSession(Request $request): JsonResponse
    {
        $user = $request->user();

        $session = ParkingSession::with(['vehicle', 'parkingArea'])
            ->where('status', 'active')
            ->whereHas('vehicle', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        if (!$session) {
            return response()->json(['message' => 'No active parking session'], 404);
        }

        return response()->json($session);
    }

    public function getCurrentRates(Request $request): JsonResponse
    {
        $request->validate([
            'area_id' => 'required|integer|exists:area_parkirs,id'
        ]);

        $areaId = $request->area_id;
        $rates = TarifCalculator::getAreaRates($areaId);

        return response()->json([
            'data' => [
                'mobil' => $rates['mobil'] ? [
                    'tarif_per_menit' => $rates['mobil']->tarif_per_menit,
                    'tarif_per_jam' => $rates['mobil']->tarif_per_jam,
                    'tarif_akumulasi_menit' => $rates['mobil']->tarif_akumulasi_menit,
                    'tarif_akumulasi_jam' => $rates['mobil']->tarif_akumulasi_jam,
                    'denda_inap_per_hari' => $rates['mobil']->denda_inap_per_hari,
                ] : null,
                'motor' => $rates['motor'] ? [
                    'tarif_per_menit' => $rates['motor']->tarif_per_menit,
                    'tarif_per_jam' => $rates['motor']->tarif_per_jam,
                    'tarif_akumulasi_menit' => $rates['motor']->tarif_akumulasi_menit,
                    'tarif_akumulasi_jam' => $rates['motor']->tarif_akumulasi_jam,
                    'denda_inap_per_hari' => $rates['motor']->denda_inap_per_hari,
                ] : null,
            ]
        ]);
    }

    public function vehicles(Request $request): JsonResponse
    {
        $user = $request->user();

        $vehicles = Kendaraan::where('user_id', $user->id)->get();

        return response()->json($vehicles);
    }
}
