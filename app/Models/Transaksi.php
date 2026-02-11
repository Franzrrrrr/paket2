<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $fillable = [
        'kendaraan_id',
        'waktu_masuk',
        'waktu_keluar',
        'durasi_jam',
        'biaya_total',
        'status',
        'user_id',
        'area_id',
        'tarif_id',
    ];

    protected $casts = [
        'waktu_masuk' => 'date',
        'waktu_keluar' => 'date',
    ];

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class);
    }

    public function areaParkir()
    {
        return $this->belongsTo(AreaParkir::class, 'area_id');
    }

    public function tarif()
    {
        return $this->belongsTo(Tarif::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
