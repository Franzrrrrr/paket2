<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingSession extends Model
{
    protected $fillable = [
        'booking_id',
        'ticket_code',
        'vehicle_id',
        'parking_area_id',
        'vehicle_type',
        'vehicle_plate',
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

    // Status constants
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';

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
        return $this->belongsTo(AreaParkir::class, 'parking_area_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function calculateDuration(): int
    {
        if (!$this->entry_time) return 0;

        $endTime = $this->exit_time ?: now();
        return $this->entry_time->diffInMinutes($endTime);
    }

    public function updateDuration(): void
    {
        $this->duration = $this->calculateDuration();
        $this->save();
    }
}
