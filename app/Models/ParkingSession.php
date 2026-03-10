<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingSession extends Model
{
    protected $fillable = [
        'ticket_code',
        'user_id',
        'vehicle_id',
        'parking_area_id',
        'entry_time',
        'exit_time',
        'duration_minutes',
        'total_price',
        'status'
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
