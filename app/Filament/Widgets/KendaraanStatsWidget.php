<?php

namespace App\Filament\Widgets;

use App\Models\Kendaraan;
use App\Models\Transaksi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KendaraanStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?array $pages = [
        'kendaraans.*',
    ];

    protected function getStats(): array
    {
        $totalVehicles = Kendaraan::count();
        $totalMobil = Kendaraan::where('jenis_kendaraan', 'Mobil')->count();
        $totalMotor = Kendaraan::where('jenis_kendaraan', 'Motor')->count();

        // Active parked vehicles
        $activeVehicles = Transaksi::with('kendaraan')
            ->whereNull('waktu_keluar')
            ->distinct('kendaraan_id')
            ->count();

        // Today's activity
        $todayActivity = Transaksi::whereDate('waktu_masuk', today())
            ->with('kendaraan')
            ->distinct('kendaraan_id')
            ->count();

        return [
            Stat::make('Total Kendaraan', $totalVehicles)
                ->description('Terdaftar dalam sistem')
                // ->descriptionIcon('heroicon-o-cars')
                ->color('primary'),

            Stat::make('Mobil', $totalMobil)
                ->description(round(($totalMobil / max($totalVehicles, 1)) * 100, 1) . '% dari total')
                // ->descriptionIcon('heroicon-o-car')
                ->color('blue'),

            Stat::make('Motor', $totalMotor)
                ->description(round(($totalMotor / max($totalVehicles, 1)) * 100, 1) . '% dari total')
                ->descriptionIcon('heroicon-o-bolt')
                ->color('orange'),

            Stat::make('Sedang Parkir', $activeVehicles)
                ->description("{$todayActivity} aktivitas hari ini")
                ->descriptionIcon('heroicon-o-map-pin')
                ->color($activeVehicles > 0 ? 'success' : 'gray'),
        ];
    }
}
