<?php

namespace App\Filament\Widgets;

use App\Models\Kendaraan;
use Filament\Widgets\ChartWidget;

class VehicleTypeDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Jenis Kendaraan';
    protected static ?int $sort = 11;

    protected static ?array $pages = [
        'dashboard',
    ];

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Kendaraan::select('jenis_kendaraan')
            ->selectRaw('count(*) as total')
            ->groupBy('jenis_kendaraan')
            ->pluck('total', 'jenis_kendaraan');

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kendaraan',
                    'data' => $counts->values()->toArray(),
                    'backgroundColor' => ['#3b82f6', '#f59e0b', '#10b981', '#ef4444'],
                ],
            ],
            'labels' => $counts->keys()->toArray(),
        ];
    }
}
