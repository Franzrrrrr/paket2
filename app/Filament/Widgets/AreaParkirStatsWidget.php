<?php

namespace App\Filament\Widgets;

use App\Models\AreaParkir;
use App\Models\Transaksi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AreaParkirStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?array $pages = [
        'area-parkirs.*',
    ];

    protected function getStats(): array
    {
        $totalAreas = AreaParkir::count();
        $totalCapacity = AreaParkir::sum('kapasitas');
        $totalOccupied = AreaParkir::sum('terisi');
        $totalAvailable = $totalCapacity - $totalOccupied;
        $occupancyRate = $totalCapacity > 0 ? round(($totalOccupied / $totalCapacity) * 100, 1) : 0;

        // Get today's transactions
        $todayTransactions = Transaksi::whereDate('waktu_masuk', today())->count();
        $activeTransactions = Transaksi::whereNull('waktu_keluar')->count();

        return [
            Stat::make('Total Area', $totalAreas)
                ->description('Semua area parkir')
                ->descriptionIcon('heroicon-o-building-office-2')
                ->color('primary'),

            Stat::make('Total Kapasitas', $totalCapacity)
                ->description("{$totalOccupied} terisi")
                ->descriptionIcon('heroicon-o-users')
                ->color($occupancyRate >= 80 ? 'danger' : ($occupancyRate >= 60 ? 'warning' : 'success'))
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Tersedia', $totalAvailable)
                ->description("{$occupancyRate}% terisi")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color($totalAvailable > 0 ? 'success' : 'danger'),

            Stat::make('Transaksi Hari Ini', $todayTransactions)
                ->description("{$activeTransactions} aktif")
                ->descriptionIcon('heroicon-o-receipt-percent')
                ->color('info'),
        ];
    }
}
