<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'parking_area_id',
        'vehicle_type',
        'vehicle_plate',
        'estimated_duration',
        'booking_time',
        'check_in_time',
        'status',
        'ticket_code',
        'notes',
    ];

    protected $casts = [
        'booking_time' => 'datetime',
        'check_in_time' => 'datetime',
        'estimated_duration' => 'integer',
    ];

    // Status constants
    const STATUS_BOOKED = 'BOOKED';
    const STATUS_CHECKED_IN = 'CHECKED_IN';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_EXPIRED = 'EXPIRED';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parkingArea(): BelongsTo
    {
        return $this->belongsTo(AreaParkir::class, 'parking_area_id');
    }

    public function parkingSession()
    {
        return $this->hasOne(ParkingSession::class);
    }

    public function canCheckIn(): bool
    {
        return $this->status === self::STATUS_BOOKED && 
               $this->booking_time->addHours(2)->isFuture(); // Can check-in within 2 hours
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_BOOKED && 
               $this->booking_time->addHours(2)->isPast();
    }

    public static function generateTicketCode(): string
    {
        do {
            $code = 'BK' . strtoupper(uniqid());
        } while (self::where('ticket_code', $code)->exists());
        
        return $code;
    }
}
