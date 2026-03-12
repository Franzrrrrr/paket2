<?php

namespace App\Filament\Widgets;

use App\Models\LogAktivitas;
use App\Models\CustomNotification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivityLogStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?array $pages = [
        'activity-logs.*',
    ];

    protected function getStats(): array
    {
        $totalLogs = LogAktivitas::count();
        $todayLogs = LogAktivitas::whereDate('created_at', today())->count();
        
        $bookingLogs = LogAktivitas::where('activity_type', 'booking')->count();
        $systemLogs = LogAktivitas::where('activity_type', 'system')->count();
        
        // Recent activity (last hour)
        $recentActivity = LogAktivitas::where('created_at', '>=', now()->subHour())->count();

        return [
            Stat::make('Total Log', $totalLogs)
                ->description("{$todayLogs} hari ini")
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Booking Logs', $bookingLogs)
                ->description(round(($bookingLogs / max($totalLogs, 1)) * 100, 1) . '% dari total')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('blue'),

            Stat::make('System Logs', $systemLogs)
                ->description(round(($systemLogs / max($totalLogs, 1)) * 100, 1) . '% dari total')
                ->descriptionIcon('heroicon-o-cog-6-tooth')
                ->color('gray'),

            Stat::make('Aktivitas Terakhir', $recentActivity)
                ->description('jam terakhir')
                ->descriptionIcon('heroicon-o-clock')
                ->color($recentActivity > 0 ? 'success' : 'gray'),
        ];
    }
}
