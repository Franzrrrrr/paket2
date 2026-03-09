<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;

class TransaksiController extends Controller
{
    public function struk(int $id)
    {
        $transaksi = Transaksi::with([
            'kendaraan',
            'areaParkir',
            'tarif',
            'user',
        ])->findOrFail($id);

        return view('filament.pages.transaksi.struk', compact('transaksi'));
    }
}
