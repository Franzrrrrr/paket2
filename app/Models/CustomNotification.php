<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

class CustomNotification extends DatabaseNotification
{
    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    // Notification types
    const TYPE_SYSTEM_ALERT = 'system_alert';
    const TYPE_BOOKING_ACTIVITY = 'booking_activity';
    const TYPE_EXIT_ACTIVITY = 'exit_activity';
    const TYPE_CLEANUP_REPORT = 'cleanup_report';
    const TYPE_SECURITY_ALERT = 'security_alert';
    const TYPE_PERFORMANCE_ALERT = 'performance_alert';

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Create a new notification
     */
    public static function createNotification($notifiable, string $type, array $data, string $priority = self::PRIORITY_MEDIUM, ?\DateTime $expiresAt = null): self
    {
        $notification = new self();
        $notification->id = (string) \Illuminate\Support\Str::uuid();
        $notification->type = $type;
        $notification->notifiable_type = get_class($notifiable);
        $notification->notifiable_id = $notifiable->getKey();
        $notification->data = $data;
        $notification->priority = $priority;
        $notification->expires_at = $expiresAt;
        $notification->created_at = now();
        $notification->updated_at = now();
        
        $notification->save();
        
        return $notification;
    }

    /**
     * Create system alert notification
     */
    public static function systemAlert(string $title, string $message, array $context = [], string $priority = self::PRIORITY_HIGH): self
    {
        $admin = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'owner']);
        })->first();

        if (!$admin) {
            throw new \Exception('No admin user found for notification');
        }

        return self::createNotification($admin, self::TYPE_SYSTEM_ALERT, [
            'title' => $title,
            'message' => $message,
            'context' => $context,
            'icon' => 'heroicon-o-exclamation-triangle',
            'color' => $priority === self::PRIORITY_CRITICAL ? 'red' : 'yellow',
        ], $priority);
    }

    /**
     * Create booking activity notification
     */
    public static function bookingActivity(string $message, array $context = []): self
    {
        return self::systemAlert('Booking Activity', $message, $context, self::PRIORITY_LOW);
    }

    /**
     * Create exit activity notification
     */
    public static function exitActivity(string $message, array $context = []): self
    {
        return self::systemAlert('Exit Activity', $message, $context, self::PRIORITY_LOW);
    }

    /**
     * Create cleanup report notification
     */
    public static function cleanupReport(string $message, array $stats = []): self
    {
        return self::systemAlert('Cleanup Report', $message, [
            'stats' => $stats,
            'icon' => 'heroicon-o-trash',
            'color' => 'blue',
        ], self::PRIORITY_MEDIUM);
    }

    /**
     * Create security alert notification
     */
    public static function securityAlert(string $message, array $context = []): self
    {
        return self::systemAlert('Security Alert', $message, $context, self::PRIORITY_CRITICAL);
    }

    /**
     * Create performance alert notification
     */
    public static function performanceAlert(string $message, array $metrics = []): self
    {
        return self::systemAlert('Performance Alert', $message, [
            'metrics' => $metrics,
            'icon' => 'heroicon-o-chart-bar',
            'color' => 'orange',
        ], self::PRIORITY_HIGH);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for priority level
     */
    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for recent notifications
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for non-expired notifications
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Check if notification is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    /**
     * Get notification color based on priority
     */
    public function getColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_CRITICAL => 'red',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_MEDIUM => 'blue',
            self::PRIORITY_LOW => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get notification icon based on type
     */
    public function getIconAttribute(): string
    {
        return $this->data['icon'] ?? 'heroicon-o-bell';
    }
}
