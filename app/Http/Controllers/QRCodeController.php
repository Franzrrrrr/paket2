<?php

namespace App\Http\Controllers;

use App\Models\AreaParkir;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QRCodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Generate QR code for parking area
     */
    public function generateAreaQR(Request $request, string $areaId): JsonResponse
    {
        $user = $request->user();
        
        $area = AreaParkir::findOrFail($areaId);
        
        // Check if user has permission to generate QR for this area
        // For now, allow all authenticated users
        
        $qrData = [
            'type' => 'parking_area',
            'area_id' => $area->id,
            'area_name' => $area->nama_area,
            'generated_by' => $user->id,
            'generated_at' => now()->toISOString(),
            'expires_at' => now()->addHours(24)->toISOString(), // QR valid for 24 hours
        ];

        // Generate QR code
        $qrCode = new QRCode();
        $qrImage = $qrCode->render(json_encode($qrData));
        $qrBase64 = base64_encode($qrImage);

        return response()->json([
            'qr_code' => 'data:image/svg+xml;base64,' . $qrBase64,
            'qr_data' => $qrData,
            'area' => [
                'id' => $area->id,
                'nama_area' => $area->nama_area,
                'alamat' => $area->alamat,
                'kapasitas' => $area->kapasitas,
                'terisi' => $area->terisi,
                'sisa' => $area->sisa,
                'status' => $area->status,
            ]
        ]);
    }

    /**
     * Generate QR code for booking
     */
    public function generateBookingQR(Request $request, string $bookingId): JsonResponse
    {
        $user = $request->user();
        
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $user->id)
            ->with('parkingArea')
            ->firstOrFail();

        $qrData = [
            'type' => 'booking_check_in',
            'booking_id' => $booking->id,
            'ticket_code' => $booking->ticket_code,
            'vehicle_type' => $booking->vehicle_type,
            'vehicle_plate' => $booking->vehicle_plate,
            'parking_area_id' => $booking->parking_area_id,
            'parking_area_name' => $booking->parkingArea->nama_area,
            'booking_time' => $booking->booking_time->toISOString(),
            'check_in_time' => $booking->check_in_time?->toISOString(),
            'status' => $booking->status,
            'generated_by' => $user->id,
            'generated_at' => now()->toISOString(),
            'expires_at' => $booking->booking_time->addHours(2)->toISOString(), // Same expiry as booking
        ];

        // Generate QR code
        $qrCode = new QRCode();
        $qrImage = $qrCode->render(json_encode($qrData));
        $qrBase64 = base64_encode($qrImage);

        return response()->json([
            'qr_code' => 'data:image/svg+xml;base64,' . $qrBase64,
            'qr_data' => $qrData,
            'booking' => [
                'id' => $booking->id,
                'ticket_code' => $booking->ticket_code,
                'vehicle_type' => $booking->vehicle_type,
                'vehicle_plate' => $booking->vehicle_plate,
                'status' => $booking->status,
                'can_check_in' => $booking->canCheckIn(),
                'area' => [
                    'id' => $booking->parkingArea->id,
                    'nama_area' => $booking->parkingArea->nama_area,
                ],
                'expires_at' => $booking->booking_time->addHours(2)->toISOString(),
            ]
        ]);
    }

    /**
     * Get all QR codes for user's active bookings
     */
    public function getMyQRCodes(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Get active bookings
        $bookings = Booking::where('user_id', $user->id)
            ->whereIn('status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])
            ->with('parkingArea')
            ->get();

        // Get all areas for QR generation
        $areas = AreaParkir::all();

        $qrCodes = [];
        foreach ($bookings as $booking) {
            $qrData = [
                'type' => 'booking_check_in',
                'booking_id' => $booking->id,
                'ticket_code' => $booking->ticket_code,
                'vehicle_type' => $booking->vehicle_type,
                'vehicle_plate' => $booking->vehicle_plate,
                'parking_area_id' => $booking->parking_area_id,
                'parking_area_name' => $booking->parkingArea->nama_area,
                'status' => $booking->status,
                'generated_at' => now()->toISOString(),
                'expires_at' => $booking->booking_time->addHours(2)->toISOString(),
            ];

            // Generate QR code
            $qrCode = new QRCode();
            $qrImage = $qrCode->render(json_encode($qrData));
            $qrBase64 = base64_encode($qrImage);

            $qrCodes[] = [
                'booking_id' => $booking->id,
                'ticket_code' => $booking->ticket_code,
                'qr_code' => 'data:image/svg+xml;base64,' . $qrBase64,
                'qr_data' => $qrData,
                'area' => [
                    'id' => $booking->parkingArea->id,
                    'nama_area' => $booking->parkingArea->nama_area,
                ],
                'status' => $booking->status,
                'expires_at' => $booking->booking_time->addHours(2)->toISOString(),
            ];
        }

        return response()->json([
            'qr_codes' => $qrCodes,
            'areas' => $areas
        ]);
    }

    /**
     * Validate QR code data
     */
    public function validateQR(Request $request): JsonResponse
    {
        $request->validate([
            'qr_data' => 'required|string'
        ]);

        $qrData = $request->qr_data;
        $user = $request->user();

        try {
            $data = json_decode($qrData, true);
            
            if (!$data) {
                return response()->json([
                    'valid' => false,
                    'message' => 'QR data tidak valid (bukan JSON)'
                ], 400);
            }

            // Check expiry
            if (isset($data['expires_at'])) {
                $expiresAt = \Carbon\Carbon::parse($data['expires_at']);
                if ($expiresAt->isPast()) {
                    return response()->json([
                        'valid' => false,
                        'message' => 'QR code sudah kadaluarsa',
                        'expired_at' => $data['expires_at']
                    ], 400);
                }
            }

            // Validate based on type
            switch ($data['type'] ?? '') {
                case 'parking_area':
                    return $this->validateAreaQR($data);
                
                case 'booking_check_in':
                    return $this->validateBookingQR($data, $user);
                
                default:
                    return response()->json([
                        'valid' => false,
                        'message' => 'Tipe QR code tidak dikenali',
                        'detected_type' => $data['type'] ?? 'unknown'
                    ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Error validating QR data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function validateAreaQR(array $data): JsonResponse
    {
        try {
            $area = AreaParkir::findOrFail($data['area_id']);
            
            return response()->json([
                'valid' => true,
                'message' => 'QR code area valid',
                'type' => 'parking_area',
                'area' => [
                    'id' => $area->id,
                    'nama_area' => $area->nama_area,
                    'alamat' => $area->alamat,
                    'kapasitas' => $area->kapasitas,
                    'terisi' => $area->terisi,
                    'sisa' => $area->sisa,
                    'status' => $area->status,
                ],
                'action_required' => 'check_in'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Area parkir tidak ditemukan',
                'area_id' => $data['area_id'] ?? 'unknown'
            ], 404);
        }
    }

    private function validateBookingQR(array $data, $user): JsonResponse
    {
        try {
            $booking = Booking::where('id', $data['booking_id'])
                ->where('ticket_code', $data['ticket_code'])
                ->with('parkingArea')
                ->firstOrFail();

            // Check if user owns this booking
            if ($booking->user_id !== $user->id) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Booking tidak dimiliki oleh user ini',
                    'booking_id' => $data['booking_id']
                ], 403);
            }

            return response()->json([
                'valid' => true,
                'message' => 'QR code booking valid',
                'type' => 'booking_check_in',
                'booking' => [
                    'id' => $booking->id,
                    'ticket_code' => $booking->ticket_code,
                    'vehicle_type' => $booking->vehicle_type,
                    'vehicle_plate' => $booking->vehicle_plate,
                    'status' => $booking->status,
                    'can_check_in' => $booking->canCheckIn(),
                    'area' => [
                        'id' => $booking->parkingArea->id,
                        'nama_area' => $booking->parkingArea->nama_area,
                    ],
                    'expires_at' => $booking->booking_time->addHours(2)->toISOString(),
                ],
                'action_required' => 'check_in'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Booking tidak ditemukan',
                'booking_id' => $data['booking_id'] ?? 'unknown',
                'ticket_code' => $data['ticket_code'] ?? 'unknown'
            ], 404);
        }
    }
}
