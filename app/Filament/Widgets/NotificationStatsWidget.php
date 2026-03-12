<?php

namespace App\Filament\Widgets;

use App\Models\CustomNotification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NotificationStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?array $pages = [
        'notifications.*',
    ];

    protected function getStats(): array
    {
        $totalNotifications = CustomNotification::count();
        $unreadNotifications = CustomNotification::whereNull('read_at')->count();
        
        $highPriority = CustomNotification::where('priority', 'high')->count();
        $criticalPriority = CustomNotification::where('priority', 'critical')->count();
        
        // Today's notifications
        $todayNotifications = CustomNotification::whereDate('created_at', today())->count();

        return [
            Stat::make('Total Notifikasi', $totalNotifications)
                ->description("{$todayNotifications} hari ini")
                ->descriptionIcon('heroicon-o-bell')
                ->color('primary'),

            Stat::make('Belum Dibaca', $unreadNotifications)
                ->description(round(($unreadNotifications / max($totalNotifications, 1)) * 100, 1) . '% dari total')
                ->descriptionIcon('heroicon-o-envelope')
                ->color($unreadNotifications > 0 ? 'warning' : 'success'),

            Stat::make('Prioritas Tinggi', $highPriority)
                ->description('memerlukan perhatian')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('orange'),

            Stat::make('Kritis', $criticalPriority)
                ->description('perlu tindakan segera')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color($criticalPriority > 0 ? 'danger' : 'success'),
        ];
    }
}
