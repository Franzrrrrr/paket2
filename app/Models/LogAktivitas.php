<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'aktivitas',
        'user_id',
        'activity_type',
        'ip_address',
        'user_agent',
        'context',
        'log_level',
        'session_id',
    ];

    protected $casts = [
        'context' => 'array',
        'user_id' => 'integer',
    ];

    // Activity types
    const TYPE_BOOKING = 'booking';
    const TYPE_EXIT = 'exit';
    const TYPE_PAYMENT = 'payment';
    const TYPE_SYSTEM = 'system';
    const TYPE_CLEANUP = 'cleanup';
    const TYPE_ADMIN = 'admin_action';
    const TYPE_AUTH = 'authentication';

    // Log levels
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new activity log
     */
    public static function logActivity(string $aktivitas, ?int $userId = null, string $activityType = self::TYPE_SYSTEM, array $context = [], string $logLevel = self::LEVEL_INFO, ?string $sessionId = null): self
    {
        return self::create([
            'aktivitas' => $aktivitas,
            'user_id' => $userId,
            'activity_type' => $activityType,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'context' => $context,
            'log_level' => $logLevel,
            'session_id' => $sessionId ?? session()->getId(),
        ]);
    }

    /**
     * Log booking activity
     */
    public static function logBooking(string $aktivitas, int $userId, array $context = []): self
    {
        return self::logActivity($aktivitas, $userId, self::TYPE_BOOKING, $context, self::LEVEL_INFO);
    }

    /**
     * Log exit activity
     */
    public static function logExit(string $aktivitas, int $userId, array $context = []): self
    {
        return self::logActivity($aktivitas, $userId, self::TYPE_EXIT, $context, self::LEVEL_INFO);
    }

    /**
     * Log system activity
     */
    public static function logSystem(string $aktivitas, array $context = [], string $logLevel = self::LEVEL_INFO): self
    {
        return self::logActivity($aktivitas, null, self::TYPE_SYSTEM, $context, $logLevel);
    }

    /**
     * Log cleanup activity
     */
    public static function logCleanup(string $aktivitas, array $context = [], string $logLevel = self::LEVEL_INFO): self
    {
        return self::logActivity($aktivitas, null, self::TYPE_CLEANUP, $context, $logLevel);
    }

    /**
     * Log admin activity
     */
    public static function logAdmin(string $aktivitas, int $userId, array $context = []): self
    {
        return self::logActivity($aktivitas, $userId, self::TYPE_ADMIN, $context, self::LEVEL_INFO);
    }

    /**
     * Scope for activity type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope for log level
     */
    public function scopeOfLevel($query, string $level)
    {
        return $query->where('log_level', $level);
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
