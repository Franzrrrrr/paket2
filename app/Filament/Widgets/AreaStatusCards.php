<?php

namespace App\Filament\Widgets;

use App\Models\AreaParkir;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AreaStatusCards extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected static ?array $pages = [
        'dashboard',
    ];

    protected function getStats(): array
    {
        $areas = AreaParkir::query()
            ->orderBy('terisi', 'desc')
            ->limit(4) // Kurangi dari 6 ke 4 untuk lebih ringkas
            ->get();

        return $areas->map(function ($area) {
            $rate = $area->kapasitas > 0
                ? round($area->terisi / $area->kapasitas * 100, 1)
                : 0;

            [$label, $color, $icon] = match (true) {
                $rate >= 100 => ['Penuh', 'danger', 'heroicon-o-x-circle'],
                $rate >= 80  => ['Hampir Penuh', 'warning', 'heroicon-o-exclamation-triangle'],
                default      => ['Tersedia', 'success', 'heroicon-o-check-circle'],
            };

            return Stat::make($area->nama_area, "{$area->terisi}/{$area->kapasitas}")
                ->description("{$rate}% — {$label}")
                ->descriptionIcon($icon)
                ->color($color)
                ->chart([7, 2, 10, 3, 15, 4, 17]) // Tambahkan mini chart
                ->chartColor($color);

        })->toArray();
    }
}
