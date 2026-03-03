<?php

namespace App\Filament\Widgets;

use App\Models\AreaParkir;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AreaStatusCards extends BaseWidget
{
    protected static ?int $sort = 1;

    protected  ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $areas = AreaParkir::all();

        return $areas->map(function ($area) {
            $rate   = $area->kapasitas > 0
                ? round($area->terisi / $area->kapasitas * 100, 1)
                : 0;

            [$label, $color, $icon] = match (true) {
                $rate >= 100 => ['Penuh',        'danger',  'heroicon-o-x-circle'],
                $rate >= 80  => ['Hampir Penuh', 'warning', 'heroicon-o-exclamation-triangle'],
                default      => ['Tersedia',     'success', 'heroicon-o-check-circle'],
            };

            return Stat::make($area->nama_area, "{$area->terisi} / {$area->kapasitas} slot")
                ->description("{$rate}% terisi — {$label}")
                ->descriptionIcon($icon)
                ->color($color);
        })->toArray();
    }
}