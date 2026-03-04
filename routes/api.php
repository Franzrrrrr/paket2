<?php

use App\Models\AreaParkir;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AreaParkirController;

Route::get('/', function (Request $request) {
    return response()->json();
});

Route::get('/area-parkir', [AreaParkirController::class, 'index']);
Route::get('/area-parkir/{id}', [AreaParkirController::class, 'show']);
Route::get('/area-parkir/parked-vehicles', [AreaParkirController::class, 'parkedVehicles']);
