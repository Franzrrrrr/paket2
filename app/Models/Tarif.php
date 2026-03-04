<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    protected $fillable = [
        'jenis_kendaraan',
        'tarif_per_jam',
        'denda_inap_per_hari',
    ];

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }
}
