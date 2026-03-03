<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class TrafficHourChart extends ChartWidget
{
    protected  ?string $heading = 'Trafik Kendaraan Per Jam (Hari Ini)';
    protected static ?int $sort = 3;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $hours = collect(range(0, 23));

        $rows = Transaksi::whereNotNull('waktu_masuk')
            ->whereDate('waktu_masuk', Carbon::today())
            ->get()
            ->groupBy(fn($t) => $t->waktu_masuk->format('H'));

        $labels = $hours->map(fn($h) => sprintf('%02d:00', $h))->toArray();
        $data   = $hours->map(fn($h) => $rows->has(sprintf('%02d', $h))
            ? $rows[sprintf('%02d', $h)]->count()
            : 0
        )->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Jumlah Kendaraan',
                    'data'            => $data,
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels,
        ];
    }
}