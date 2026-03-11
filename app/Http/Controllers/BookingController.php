<?php

namespace App\Http\Controllers;

use App\Models\AreaParkir;
use App\Models\ParkingSession;
use App\Models\Kendaraan;
use App\Models\Transaksi;
use App\Models\LogAktivitas;
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
            'vehicle_id' => 'required|exists:kendaraans,id',
            'parking_area_id' => 'required|exists:area_parkirs,id',
            'estimated_duration' => 'nullable|integer|min:1'
        ]);

        $user = $request->user();
        $vehicle = Kendaraan::findOrFail($request->vehicle_id);
        $parkingArea = AreaParkir::findOrFail($request->parking_area_id);

        // Check if vehicle belongs to user
        if ($vehicle->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized: Vehicle does not belong to user'], 403);
        }

        // Check if parking area has available space
        if ($parkingArea->terisi >= $parkingArea->kapasitas) {
            return response()->json(['message' => 'Parking area is full'], 422);
        }

        // Check if vehicle already has active session
        $activeSession = ParkingSession::where('vehicle_id', $vehicle->id)
            ->where('status', 'active')
            ->first();

        if ($activeSession) {
            return response()->json(['message' => 'Vehicle already has active parking session'], 422);
        }

        // Generate unique ticket code
        do {
            $ticketCode = 'PK-' . strtoupper(Str::random(8));
        } while (ParkingSession::where('ticket_code', $ticketCode)->exists());

        // Create parking session
        $parkingSession = ParkingSession::create([
            'ticket_code' => $ticketCode,
            'vehicle_id' => $vehicle->id,
            'parking_area_id' => $parkingArea->id,
            'entry_time' => now(),
            'status' => 'active'
        ]);

        // Update parking area occupancy
        $parkingArea->increment('terisi');

        // Create transaction record
        $transaksi = Transaksi::create([
            'id_user' => $user->id,
            'id_kendaraan' => $vehicle->id,
            'id_area' => $parkingArea->id,
            'waktu_masuk' => now(),
            'status' => 'masuk',
            'ticket_code' => $ticketCode
        ]);

        // Log activity
        LogAktivitas::create([
            'user_id' => $user->id,
            'aktivitas' => "User booked parking at {$parkingArea->nama_area} with ticket {$ticketCode}"
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
        $parkingSession = ParkingSession::where('ticket_code', $request->ticket_code)
            ->with(['vehicle', 'parkingArea'])
            ->firstOrFail();

        // Verify ownership or admin role
        if ($parkingSession->vehicle->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($parkingSession->status !== 'active') {
            return response()->json(['message' => 'Parking session is not active'], 422);
        }

        $exitTime = now();
        $duration = $exitTime->diffInMinutes($parkingSession->entry_time);

        // Update parking session
        $parkingSession->update([
            'exit_time' => $exitTime,
            'duration' => $duration,
            'status' => 'completed'
        ]);

        // Update parking area occupancy
        $parkingSession->parkingArea->decrement('terisi');

        // Update transaction
        $transaksi = Transaksi::where('ticket_code', $request->ticket_code)->first();
        if ($transaksi) {
            $transaksi->update([
                'waktu_keluar' => $exitTime,
                'status' => 'keluar'
            ]);
        }

        // Calculate price (example: Rp 2000 per hour, minimum 1 hour)
        $hours = ceil($duration / 60);
        $totalPrice = $hours * 2000;

        // Log activity
        LogAktivitas::create([
            'user_id' => $user->id,
            'aktivitas' => "Vehicle exited from {$parkingSession->parkingArea->nama_area} with ticket {$request->ticket_code}. Duration: {$duration} minutes, Price: Rp {$totalPrice}"
        ]);

        return response()->json([
            'message' => 'Exit successful',
            'data' => [
                'ticket_code' => $parkingSession->ticket_code,
                'duration_minutes' => $duration,
                'total_price' => $totalPrice,
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

    public function vehicles(Request $request): JsonResponse
    {
        \Log::info('Fetching vehicles for user', ['user_id' => $request->user()->id]);
        $user = $request->user();

        $vehicles = Kendaraan::where('user_id', $user->id)->get();

        return response()->json($vehicles);
    }
}
