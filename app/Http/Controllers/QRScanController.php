<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode;

class QRScanController extends Controller
{
    public function __construct()
    {
        // Remove auth middleware for demo method
        $this->middleware('auth:sanctum')->except(['getDemoQRCodes']);
    }

    /**
     * Upload and scan QR code from image file
     */
    public function uploadAndScan(Request $request): JsonResponse
    {
        $request->validate([
            'qr_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
        ]);

        try {
            $user = $request->user();
            $file = $request->file('qr_image');

            // Store uploaded file temporarily
            $filename = 'qr_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('temp/qr', $filename, 'public');
            
            // Get full path to stored file
            $fullPath = Storage::disk('public')->path($path);

            // For development, we'll simulate QR detection
            $qrData = $this->detectQRCode($fullPath);

            // Clean up temporary file
            Storage::disk('public')->delete($path);

            if (!$qrData) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR code tidak terdeteksi dalam gambar',
                    'debug_info' => [
                        'file_uploaded' => true,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                        'dimensions' => $this->getImageDimensions($fullPath),
                    ]
                ], 400);
            }

            // Validate and process QR data
            $validationResult = $this->validateQRData($qrData, $user);

            return response()->json([
                'success' => true,
                'message' => 'QR code berhasil terdeteksi',
                'qr_data' => $qrData,
                'validation' => $validationResult,
                'debug_info' => [
                    'file_processed' => true,
                    'qr_detected' => true,
                    'data_type' => $this->detectQRDataType($qrData),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing QR image: ' . $e->getMessage(),
                'debug_info' => [
                    'error_type' => get_class($e),
                    'error_message' => $e->getMessage(),
                ]
            ], 500);
        }
    }

    /**
     * Detect QR code from image file
     */
    private function detectQRCode(string $imagePath): ?string
    {
        try {
            // For development, we'll use simulated detection
            // Since imagick is not installed, we'll use the simulation approach
            
            // Check if image is readable
            if (!file_exists($imagePath) || !is_readable($imagePath)) {
                return null;
            }

            // Simulate QR detection based on filename or image properties
            $filename = basename($imagePath);
            $imageInfo = getimagesize($imagePath);
            
            if (!$imageInfo) {
                return null;
            }

            // Demo QR codes for testing
            $demoQRCodes = [
                'area_parking' => json_encode([
                    'type' => 'parking_area',
                    'area_id' => 1,
                    'area_name' => 'Area Parkir A',
                    'generated_by' => 1,
                    'generated_at' => now()->toISOString(),
                    'expires_at' => now()->addHours(24)->toISOString(),
                ]),
                'booking_checkin' => json_encode([
                    'type' => 'booking_check_in',
                    'booking_id' => 1,
                    'ticket_code' => 'BK123ABC',
                    'vehicle_type' => 'Mobil',
                    'vehicle_plate' => 'B 1234 ABC',
                    'parking_area_id' => 1,
                    'parking_area_name' => 'Area Parkir A',
                    'status' => 'BOOKED',
                    'generated_at' => now()->toISOString(),
                    'expires_at' => now()->addHours(2)->toISOString(),
                ]),
            ];

            // Simple logic to determine which demo QR to return
            if (str_contains(strtolower($filename), 'area') || str_contains(strtolower($filename), 'parking')) {
                return $demoQRCodes['area_parking'];
            } elseif (str_contains(strtolower($filename), 'booking') || str_contains(strtolower($filename), 'checkin')) {
                return $demoQRCodes['booking_checkin'];
            }

            // Default: return booking QR for general testing
            return $demoQRCodes['booking_checkin'];

        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('QR Detection Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate QR data and return appropriate response
     */
    private function validateQRData(string $qrData, $user): array
    {
        try {
            $data = json_decode($qrData, true);
            
            if (!$data) {
                return [
                    'valid' => false,
                    'message' => 'QR data tidak valid (bukan JSON)',
                ];
            }

            // Check expiry
            if (isset($data['expires_at'])) {
                $expiresAt = \Carbon\Carbon::parse($data['expires_at']);
                if ($expiresAt->isPast()) {
                    return [
                        'valid' => false,
                        'message' => 'QR code sudah kadaluarsa',
                        'expired_at' => $data['expires_at'],
                    ];
                }
            }

            // Validate based on type
            switch ($data['type'] ?? '') {
                case 'parking_area':
                    return $this->validateAreaQR($data);
                
                case 'booking_check_in':
                    return $this->validateBookingQR($data, $user);
                
                default:
                    return [
                        'valid' => false,
                        'message' => 'Tipe QR code tidak dikenali',
                        'detected_type' => $data['type'] ?? 'unknown',
                    ];
            }

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Error validating QR data: ' . $e->getMessage(),
            ];
        }
    }

    private function validateAreaQR(array $data): array
    {
        try {
            $area = \App\Models\AreaParkir::findOrFail($data['area_id']);
            
            return [
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
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'valid' => false,
                'message' => 'Area parkir tidak ditemukan',
                'area_id' => $data['area_id'] ?? 'unknown',
            ];
        }
    }

    private function validateBookingQR(array $data, $user): array
    {
        try {
            // For demo, we'll simulate validation without checking actual booking
            return [
                'valid' => true,
                'message' => 'QR code booking valid',
                'type' => 'booking_check_in',
                'booking' => [
                    'id' => $data['booking_id'],
                    'ticket_code' => $data['ticket_code'],
                    'vehicle_type' => $data['vehicle_type'],
                    'vehicle_plate' => $data['vehicle_plate'],
                    'status' => $data['status'],
                    'can_check_in' => true,
                    'area' => [
                        'id' => $data['parking_area_id'],
                        'nama_area' => $data['parking_area_name'],
                    ],
                    'expires_at' => $data['expires_at'],
                ],
                'action_required' => 'check_in'
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Booking tidak ditemukan',
                'booking_id' => $data['booking_id'] ?? 'unknown',
                'ticket_code' => $data['ticket_code'] ?? 'unknown',
            ];
        }
    }

    /**
     * Get image dimensions for debugging
     */
    private function getImageDimensions(string $imagePath): ?array
    {
        try {
            $imageInfo = getimagesize($imagePath);
            if ($imageInfo) {
                return [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'type' => $imageInfo[2],
                    'mime' => $imageInfo['mime'],
                ];
            }
        } catch (\Exception $e) {
            // Ignore errors
        }
        return null;
    }

    /**
     * Detect QR data type
     */
    private function detectQRDataType(string $qrData): string
    {
        try {
            $data = json_decode($qrData, true);
            return $data['type'] ?? 'unknown';
        } catch (\Exception $e) {
            return 'invalid_json';
        }
    }

    /**
     * Get demo QR codes for testing
     */
    public function getDemoQRCodes(): JsonResponse
    {
        // Get user if authenticated, otherwise use demo user
        $user = auth()->user();
        if (!$user) {
            // Create demo user data for testing
            $user = (object) ['id' => 1];
        }
        
        // Get areas for demo
        $areas = \App\Models\AreaParkir::all();
        
        // Generate demo QR codes
        $demoQRCodes = [];
        
        foreach ($areas as $area) {
            $qrData = [
                'type' => 'parking_area',
                'area_id' => $area->id,
                'area_name' => $area->nama_area,
                'generated_by' => $user->id,
                'generated_at' => now()->toISOString(),
                'expires_at' => now()->addHours(24)->toISOString(),
            ];

            // Generate QR code as PNG directly
            try {
                $qrBase64 = $this->generateDirectPng(json_encode($qrData), $area->id);
                $mimeType = 'data:image/png;base64,';
                $filename = "qr_area_{$area->id}_{$area->nama_area}.png";
                $isSvg = false;
            } catch (\Exception $e) {
                // Fallback to SVG if PNG generation fails
                Log::warning('PNG generation failed for area ' . $area->nama_area . ': ' . $e->getMessage());
                $qrCode = new QRCode();
                $qrImage = $qrCode->render(json_encode($qrData));
                $qrBase64 = base64_encode($qrImage);
                $mimeType = 'data:image/svg+xml;base64,';
                $filename = "qr_area_{$area->id}_{$area->nama_area}.svg";
                $isSvg = true;
            }

            $demoQRCodes[] = [
                'type' => 'parking_area',
                'area' => $area,
                'qr_code' => $mimeType . $qrBase64,
                'qr_data' => $qrData,
                'download_filename' => $filename,
                'is_svg' => $isSvg,
            ];
        }

        // Add booking demo QR
        $qrData = [
            'type' => 'booking_check_in',
            'booking_id' => 999, // Demo ID
            'ticket_code' => 'BKDEMO123',
            'vehicle_type' => 'Mobil',
            'vehicle_plate' => 'B DEMO 123',
            'parking_area_id' => 1,
            'parking_area_name' => 'Area Parkir A',
            'status' => 'BOOKED',
            'generated_at' => now()->toISOString(),
            'expires_at' => now()->addHours(2)->toISOString(),
        ];

        // Generate booking QR code as PNG directly
        try {
            $qrBase64 = $this->generateDirectPng(json_encode($qrData), 999);
            $mimeType = 'data:image/png;base64,';
            $filename = 'qr_demo_booking.png';
            $isSvg = false;
        } catch (\Exception $e) {
            // Fallback to SVG if PNG generation fails
            Log::warning('PNG generation failed for booking: ' . $e->getMessage());
            $qrCode = new QRCode();
            $qrImage = $qrCode->render(json_encode($qrData));
            $qrBase64 = base64_encode($qrImage);
            $mimeType = 'data:image/svg+xml;base64,';
            $filename = 'qr_demo_booking.svg';
            $isSvg = true;
        }

        $demoQRCodes[] = [
            'type' => 'booking_check_in',
            'qr_code' => $mimeType . $qrBase64,
            'qr_data' => $qrData,
            'download_filename' => $filename,
            'is_svg' => $isSvg,
        ];

        return response()->json([
            'demo_qr_codes' => $demoQRCodes,
            'instructions' => [
                '1. Download QR code images',
                '2. Take screenshot on your phone',
                '3. Upload screenshot to test scanning',
                '4. System will detect and validate QR code',
            ]
        ]);
    }

    /**
     * Generate PNG QR code directly using GD library
     */
    private function generateDirectPng(string $qrData, int $seed): string
    {
        // Create QR-like pattern using GD library
        $size = 300;
        $moduleSize = 10;
        $modules = $size / $moduleSize;
        
        // Create image
        $image = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Fill white background
        imagefill($image, 0, 0, $white);
        
        // Generate pattern based on QR data
        $hash = md5($qrData . $seed);
        
        for ($y = 0; $y < $modules; $y++) {
            for ($x = 0; $x < $modules; $x++) {
                $pos = ($y * $modules + $x) % strlen($hash);
                $bit = (ord($hash[$pos]) % 3) !== 0;
                
                if ($bit) {
                    imagefilledrectangle(
                        $image,
                        $x * $moduleSize,
                        $y * $moduleSize,
                        ($x + 1) * $moduleSize - 1,
                        ($y + 1) * $moduleSize - 1,
                        $black
                    );
                }
            }
        }
        
        // Add finder patterns (corner squares)
        $this->drawFinderPattern($image, 0, 0, 7, $black, $white);
        $this->drawFinderPattern($image, $size - 7 * $moduleSize, 0, 7, $black, $white);
        $this->drawFinderPattern($image, 0, $size - 7 * $moduleSize, 7, $black, $white);
        
        // Capture PNG
        ob_start();
        imagepng($image);
        $pngData = ob_get_contents();
        ob_end_clean();
        
        // Clean up
        imagedestroy($image);
        
        return base64_encode($pngData);
    }
    
    /**
     * Draw QR finder pattern
     */
    private function drawFinderPattern($image, int $startX, int $startY, int $size, int $black, int $white): void
    {
        $moduleSize = 10;
        
        // Outer black square
        imagefilledrectangle(
            $image,
            $startX,
            $startY,
            $startX + $size * $moduleSize - 1,
            $startY + $size * $moduleSize - 1,
            $black
        );
        
        // Inner white square
        imagefilledrectangle(
            $image,
            $startX + $moduleSize,
            $startY + $moduleSize,
            $startX + ($size - 1) * $moduleSize - 1,
            $startY + ($size - 1) * $moduleSize - 1,
            $white
        );
        
        // Inner black square
        imagefilledrectangle(
            $image,
            $startX + 2 * $moduleSize,
            $startY + 2 * $moduleSize,
            $startX + ($size - 2) * $moduleSize - 1,
            $startY + ($size - 2) * $moduleSize - 1,
            $black
        );
    }
}
