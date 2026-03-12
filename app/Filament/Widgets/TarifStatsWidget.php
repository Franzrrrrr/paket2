<?php

namespace App\Filament\Widgets;

use App\Models\Tarif;
use App\Models\AreaParkir;
use App\Models\Transaksi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TarifStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?array $pages = [
        'tarifs.*',
    ];

    protected function getStats(): array
    {
        $totalTarifs = Tarif::count();
        $tarifMobil = Tarif::where('jenis_kendaraan', 'Mobil')->count();
        $tarifMotor = Tarif::where('jenis_kendaraan', 'Motor')->count();

        // Areas with tariffs
        $areasWithTarif = AreaParkir::whereHas('tarifs')->count();
        $totalAreas = AreaParkir::count();

        // Today's revenue estimation
        $todayRevenue = Transaksi::whereDate('waktu_masuk', today())
            ->whereNotNull('biaya_total')
            ->sum('biaya_total');

        return [
            Stat::make('Total Tarif', $totalTarifs)
                ->description('Konfigurasi tarif')
                ->descriptionIcon('heroicon-o-tag')
                ->color('primary'),

            Stat::make('Tarif Mobil', $tarifMobil)
                ->description(round(($tarifMobil / max($totalTarifs, 1)) * 100, 1) . '% dari total')
                // ->descriptionIcon('heroicon-o-car')
                ->color('blue'),

            Stat::make('Tarif Motor', $tarifMotor)
                ->description(round(($tarifMotor / max($totalTarifs, 1)) * 100, 1) . '% dari total')
                ->descriptionIcon('heroicon-o-bolt')
                ->color('orange'),

            Stat::make('Area Tercover', $areasWithTarif)
                ->description("dari {$totalAreas} area")
                ->descriptionIcon('heroicon-o-map')
                ->color($areasWithTarif == $totalAreas ? 'success' : 'warning'),
        ];
    }
}
