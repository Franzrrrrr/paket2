<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParkingSession;
use App\Models\AreaParkir;
use App\Models\Kendaraan;
use App\Models\Transaksi;
use App\Models\LogAktivitas;
use App\Services\TarifCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Create a new booking reservation
     */
    public function book(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_type' => 'required|string|in:Mobil,Motor',
            'vehicle_plate' => 'required|string|max:20',
            'parking_area_id' => 'required|integer|exists:area_parkirs,id',
            'estimated_duration' => 'nullable|integer|min:1|max:480' // max 8 hours
        ]);

        $user = $request->user();
        $parkingArea = AreaParkir::findOrFail($request->parking_area_id);

        // Check if user has active bookingwwwwww
        $activeBooking = Booking::where('user_id', $user->id)
            ->whereIn('status', [Booking::STATUS_BOOKED ])
            ->first();

        if ($activeBooking) {
            return response()->json([
                'message' => 'Anda sudah memiliki booking aktif',
                'booking' => $activeBooking
            ], 422);
        }

        // Generate unique ticket code
        $ticketCode = Booking::generateTicketCode();

        // Create booking
        $booking = Booking::create([
            'user_id' => $user->id,
            'parking_area_id' => $request->parking_area_id,
            'vehicle_type' => $request->vehicle_type,
            'vehicle_plate' => strtoupper(str_replace(' ', '', $request->vehicle_plate)),
            'estimated_duration' => $request->estimated_duration,
            'booking_time' => now(),
            'status' => Booking::STATUS_BOOKED,
            'ticket_code' => $ticketCode,
            'notes' => $request->notes,
        ]);

        LogAktivitas::logBooking(
            "Booking berhasil dibuat: {$ticketCode}",
            $user->id,
            [
                'booking_id' => $booking->id,
                'ticket_code' => $ticketCode,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_plate' => $request->vehicle_plate,
                'parking_area_id' => $request->parking_area_id,
                'estimated_duration' => $request->estimated_duration,
            ]
        );

        return response()->json([
            'message' => 'Booking berhasil dibuat',
            'booking' => [
                'id' => $booking->id,
                'ticket_code' => $booking->ticket_code,
                'vehicle_type' => $booking->vehicle_type,
                'vehicle_plate' => $booking->vehicle_plate,
                'parking_area' => [
                    'id' => $parkingArea->id,
                    'nama_area' => $parkingArea->nama_area,
                    'alamat' => $parkingArea->alamat,
                ],
                'booking_time' => $booking->booking_time->toISOString(),
                'estimated_duration' => $booking->estimated_duration,
                'status' => $booking->status,
                'can_check_in' => $booking->canCheckIn(),
                'expires_at' => $booking->booking_time->addHours(2)->toISOString(),
            ]
        ], 201);
    }

    /**
     * Check-in from booking
     */
    public function checkIn(Request $request): JsonResponse
    {

        $request->validate([
            'ticket_code' => 'required|string|exists:bookings,ticket_code'
        ]);

        $user = $request->user();
        $booking = Booking::where('ticket_code', $request->ticket_code)
            ->with('parkingArea')
            ->firstOrFail();

        // Validate booking ownership
        if ($booking->user_id !== $user->id) {
            return response()->json([
                'message' => 'Booking tidak ditemukan atau tidak valid'
            ], 404);
        }

        // Validate booking status
        if ($booking->status !== Booking::STATUS_BOOKED) {
            return response()->json([
                'message' => 'Booking sudah check-in atau tidak valid',
                'status' => $booking->status
            ], 422);
        }

        // Validate check-in time
        if (!$booking->canCheckIn()) {
            return response()->json([
                'message' => 'Booking sudah kadaluarsa',
                'expired_at' => $booking->booking_time->addHours(2)->toISOString()
            ], 422);
        }

        // Check if parking session already exists for this booking
        $existingSession = ParkingSession::where('ticket_code', $booking->ticket_code)->first();
        if ($existingSession) {
            return response()->json([
                'message' => 'Booking sudah check-in sebelumnya',
                'session_id' => $existingSession->id
            ], 422);
        }

        // Check parking area capacity
        $parkingArea = $booking->parkingArea;
        // \Log::info($parkingArea->toArray());
        if ($parkingArea->terisi >= $parkingArea->kapasitas) {
            return response()->json([
                'message' => 'Area parkir sudah penuh'
            ], 422);
        }

        // Create or find vehicle
        $vehicle = Kendaraan::firstOrCreate(
            ['plat_nomor' => $booking->vehicle_plate],
            [
                'jenis_kendaraan' => $booking->vehicle_type,
                'user_id' => $user->id,
            ]
        );

        // Get tarif_id from area parkir based on vehicle type
        $tarif = $parkingArea->tarifs()
            ->where('jenis_kendaraan', $booking->vehicle_type)
            ->first();

        \Log::info('tarif id: ' . $tarif->id);

        // Create parking session
        $parkingSession = ParkingSession::create([
            'booking_id' => $booking->id,
            'ticket_code' => $booking->ticket_code,
            'vehicle_id' => $vehicle->id,
            'parking_area_id' => $booking->parking_area_id,
            'vehicle_type' => $booking->vehicle_type,
            'vehicle_plate' => $booking->vehicle_plate,
            'entry_time' => now(),
            'status' => ParkingSession::STATUS_ACTIVE,
        ]);

        // Create transaction
        $transaksi = Transaksi::create([
            'kendaraan_id' => $vehicle->id,
            'waktu_masuk' => now(),
            'status' => 'masuk',
            'tarif_id' => $tarif->id,
            'user_id' => $user->id,
            'area_id' => $booking->parking_area_id,
        ]);

        // Update booking status
        $booking->update([
            'check_in_time' => now(),
            'status' => Booking::STATUS_CHECKED_IN,
        ]);

        // Update parking area capacity
        $parkingArea->increment('terisi');

        LogAktivitas::logCheckIn(
            "Check-in berhasil: {$booking->ticket_code}",
            $user->id,
            [
                'booking_id' => $booking->id,
                'parking_session_id' => $parkingSession->id,
                'transaksi_id' => $transaksi->id,
                'vehicle_id' => $vehicle->id,
                'parking_area_id' => $booking->parking_area_id,
                'entry_time' => $parkingSession->entry_time,
            ]
        );

        return response()->json([
            'message' => 'Check-in berhasil',
            'parking_session' => [
                'id' => $parkingSession->id,
                'ticket_code' => $parkingSession->ticket_code,
                'entry_time' => $parkingSession->entry_time->toISOString(),
                'status' => $parkingSession->status,
                'vehicle' => [
                    'id' => $vehicle->id,
                    'plat_nomor' => $vehicle->plat_nomor,
                    'jenis_kendaraan' => $vehicle->jenis_kendaraan,
                ],
                'parking_area' => [
                    'id' => $parkingArea->id,
                    'nama_area' => $parkingArea->nama_area,
                ],
            ]
        ]);
    }

    /**
     * Get user bookings
     */
    public function myBookings(Request $request): JsonResponse
    {
        $user = $request->user();

        $bookings = Booking::where('user_id', $user->id)
            ->with('parkingArea')
            ->with('parkingSession')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'ticket_code' => $booking->ticket_code,
                    'vehicle_type' => $booking->vehicle_type,
                    'vehicle_plate' => $booking->vehicle_plate,
                    'parking_area' => [
                        'id' => $booking->parkingArea->id,
                        'nama_area' => $booking->parkingArea->nama_area,
                        'alamat' => $booking->parkingArea->alamat,
                    ],
                    'booking_time' => $booking->booking_time->toISOString(),
                    'check_in_time' => $booking->check_in_time?->toISOString(),
                    'estimated_duration' => $booking->estimated_duration,
                    'status' => $booking->status,
                    'can_check_in' => $booking->canCheckIn(),
                    'expires_at' => $booking->booking_time->addHours(2)->toISOString(),
                    'has_active_session' => $booking->parkingSession?->isActive() ?? false,
                ];
            });

        return response()->json([
            'bookings' => $bookings
        ]);
    }

    /**
     * Cancel booking
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $booking = Booking::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($booking->status !== Booking::STATUS_BOOKED) {
            return response()->json([
                'message' => 'Booking tidak dapat dibatalkan'
            ], 422);
        }

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
        ]);

        LogAktivitas::logBooking(
            "Booking dibatalkan: {$booking->ticket_code}",
            $user->id,
            [
                'booking_id' => $booking->id,
                'ticket_code' => $booking->ticket_code,
                'cancelled_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Booking berhasil dibatalkan'
        ]);
    }
}
