<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    protected $fillable = [
        'jenis_kendaraan',
        'tarif_per_menit',
        'tarif_per_jam',
        'denda_inap_per_hari',
        'tarif_akumulasi_menit',
        'tarif_akumulasi_jam',
    ];

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function areaParkirs()
    {
        return $this->belongsToMany(AreaParkir::class, 'area_parkir_tarif', 'tarif_id', 'area_parkir_id');
    }
}
