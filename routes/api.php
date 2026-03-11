<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AreaParkirController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ParkingSessionController;

Route::get('/', function (Request $request) {
    return response()->json();
});

Route::post('/login', [AuthController::class, 'login']);

Route::get('/area-parkir', [AreaParkirController::class, 'index']);
Route::get('/area-parkir/{id}', [AreaParkirController::class, 'show']);
Route::get('/area-parkir/parked-vehicles', [AreaParkirController::class, 'parkedVehicles']);

Route::middleware('auth:sanctum')->group(function(){
    // Booking routes
    Route::post('/booking', [BookingController::class, 'book']);
    Route::post('/booking/exit', [BookingController::class, 'exit']);
    Route::get('/booking/sessions', [BookingController::class, 'userSessions']);
    Route::get('/booking/active', [BookingController::class, 'activeSession']);
    Route::get('/vehicles', [BookingController::class, 'vehicles']);

    // Parking session routes
    Route::get('/parking-sessions', [ParkingSessionController::class, 'index']);
    Route::get('/parking-sessions/{ticketCode}', [ParkingSessionController::class, 'show']);
    Route::get('/parking-sessions/active', [ParkingSessionController::class, 'activeSessions']);
    Route::post('/parking-sessions/{ticketCode}/cancel', [ParkingSessionController::class, 'cancel']);
});
