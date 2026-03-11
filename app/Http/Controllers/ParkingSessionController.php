<?php

namespace App\Http\Controllers;

use App\Models\ParkingSession;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ParkingSessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only admin/owner can see all sessions
        if (!$user->hasRole(['admin', 'owner'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sessions = ParkingSession::with(['vehicle.user', 'parkingArea'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->parking_area_id, function ($query, $areaId) {
                $query->where('parking_area_id', $areaId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($sessions);
    }

    public function show(string $ticketCode): JsonResponse
    {
        $user = request()->user();

        $session = ParkingSession::with(['vehicle.user', 'parkingArea'])
            ->where('ticket_code', $ticketCode)
            ->firstOrFail();

        // Check ownership or admin role
        if ($session->vehicle->user_id !== $user->id && !$user->hasRole(['admin', 'owner'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($session);
    }

    public function activeSessions(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only admin/owner can see all active sessions
        if (!$user->hasRole(['admin', 'owner'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sessions = ParkingSession::with(['vehicle.user', 'parkingArea'])
            ->where('status', 'active')
            ->when($request->parking_area_id, function ($query, $areaId) {
                $query->where('parking_area_id', $areaId);
            })
            ->orderBy('entry_time', 'desc')
            ->get();

        return response()->json([
            'total' => $sessions->count(),
            'data' => $sessions
        ]);
    }

    public function cancel(Request $request, string $ticketCode): JsonResponse
    {
        $user = $request->user();

        $session = ParkingSession::with(['vehicle', 'parkingArea'])
            ->where('ticket_code', $ticketCode)
            ->where('status', 'active')
            ->firstOrFail();

        // Check ownership or admin role
        if ($session->vehicle->user_id !== $user->id && !$user->hasRole(['admin', 'owner'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $session->update(['status' => 'cancelled']);

        // Update parking area occupancy
        $session->parkingArea->decrement('terisi');

        // Log activity
        LogAktivitas::create([
            'user_id' => $user->id,
            'aktivitas' => "Parking booking cancelled for {$session->ticket_code} by {$user->name}"
        ]);

        return response()->json(['message' => 'Booking cancelled successfully']);
    }
}
