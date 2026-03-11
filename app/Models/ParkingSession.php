<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingSession extends Model
{
    protected $fillable = [
        'ticket_code',
        'vehicle_id',
        'parking_area_id',
        'entry_time',
        'exit_time',
        'duration',
        'status'
    ];

    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
        'duration' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Kendaraan::class);
    }

    public function parkingArea()
    {
        return $this->belongsTo(AreaParkir::class);
    }
}
