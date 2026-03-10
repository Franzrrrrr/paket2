<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


class AreaParkir extends Model
{
    protected $fillable = [
        'nama_area',
        'kapasitas',
        'terisi',
        'latitude',
        'longitude',
        'alamat'
    ];

    protected function location(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->latitude && $this->longitude
                ? "{$this->latitude}, {$this->longitude}"
                : null
        );
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'area_id');
    }
}
