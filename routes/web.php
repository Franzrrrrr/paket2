<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});


use App\Http\Controllers\TransaksiController;

Route::middleware(['auth'])->group(function () {
    Route::get('/transaksi/{id}/struk', [TransaksiController::class, 'struk'])
        ->name('transaksi.struk');
});
