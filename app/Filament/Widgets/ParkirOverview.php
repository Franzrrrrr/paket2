<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ParkirOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // basic summary cards
        $totalArea = \App\Models\AreaParkir::count();
        $totalParked = \App\Models\Transaksi::whereNull('waktu_keluar')->count();
        $todayRevenue = \App\Models\Transaksi::whereDate('waktu_keluar', now()->toDateString())
            ->sum('biaya_total');

        return [
            Stat::make('Area', $totalArea),
            Stat::make('Sedang Parkir', $totalParked),
            Stat::make('Pendapatan Hari Ini', 'Rp '.number_format($todayRevenue,0,',','.')),
        ];
    }
}
