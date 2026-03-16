<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TransaksiController;

// Redirect root to booking page (for customers)
Route::get('/', function () {
    return redirect('/booking');
});

// Public booking routes (for customers) - no auth middleware
Route::middleware(['web'])->group(function () {
    Route::get('/booking', [HomeController::class, 'bookingPage'])->name('booking.page');
    Route::get('/my-bookings', [HomeController::class, 'myBookings'])->name('my-bookings.page');
    Route::get('/qr-codes', [HomeController::class, 'qrCodesPage'])->name('qr-codes.page');
});

// Protected routes (for authenticated users)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/transaksi/{id}/struk', [TransaksiController::class, 'struk'])
        ->name('transaksi.struk');
});

// Filament will handle /admin and all admin routes automatically
