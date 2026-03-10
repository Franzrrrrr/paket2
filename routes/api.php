<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AreaParkirController;
use App\Http\Controllers\AuthController;

Route::get('/', function (Request $request) {
    return response()->json();
});

Route::middleware('auth:sanctum')->group(function(){

});
Route::get('/area-parkir', [AreaParkirController::class, 'index']);
Route::get('/area-parkir/{id}', [AreaParkirController::class, 'show']);
Route::get('/area-parkir/parked-vehicles', [AreaParkirController::class, 'parkedVehicles']);
Route::post('/login', [AuthController::class, 'login']);
