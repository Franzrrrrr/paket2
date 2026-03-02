<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return response()->json(['message' => [
        'id' => '1',
        'name' => 'nama',
        'email' => 'email@gmail.com',
    ]]);
});
