<?php

namespace App\Filament\Widgets;

use App\Models\AreaParkir;
use Filament\Widgets\ChartWidget;

class OccupancyRateChart extends ChartWidget
{
    protected ?string $heading = 'Tingkat Okupansi Per Area';
    protected static ?int $sort = 8;

    protected static ?array $pages = [
        'dashboard',
    ];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $areas = AreaParkir::all();

        $labels = $areas->pluck('nama_area')->toArray();
        $data   = $areas->map(fn($area) => $area->kapasitas > 0
            ? round(100 * $area->terisi / $area->kapasitas, 1)
            : 0
        )->toArray();

        $backgroundColors = $areas->map(function ($area) {
            $rate = $area->kapasitas > 0 ? $area->terisi / $area->kapasitas * 100 : 0;
            if ($rate >= 100) return '#ef4444'; // merah - penuh
            if ($rate >= 80)  return '#f59e0b'; // kuning - hampir penuh
            return '#10b981';                   // hijau - tersedia
        })->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Okupansi (%)',
                    'data'            => $data,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'max' => 100,
                    'ticks' => [
                        'callback' => 'function(value) { return value + "%" }',
                    ],
                ],
            ],
        ];
    }
}
