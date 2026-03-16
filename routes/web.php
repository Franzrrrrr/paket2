<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TransaksiController;

// Simple health check for Railway (before any middleware)
Route::get('/up', function () {
    return response('OK', 200);
});

// Simple ping endpoint
Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString()
    ], 200);
});

// Detailed health check (with database)
Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'database' => 'connected'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => 'Database connection failed'
        ], 503);
    }
});

// Redirect root to booking page (for customers)
Route::get('/', function () {
    return redirect('/booking');
});

// Protected routes (for authenticated users)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/transaksi/{id}/struk', [TransaksiController::class, 'struk'])
        ->name('transaksi.struk');
});
