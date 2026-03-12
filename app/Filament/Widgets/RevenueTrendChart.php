<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RevenueTrendChart extends ChartWidget
{
    protected ?string $heading = 'Tren Pendapatan (7 Hari)';
    protected static ?int $sort = 9;

    protected static ?array $pages = [
        'dashboard',
    ];

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = Carbon::today()->subDays(6);

        $rows = Transaksi::whereNotNull('biaya_total')
            ->whereBetween('waktu_keluar', [$start, Carbon::now()])
            ->get()
            ->groupBy(fn($t) => $t->waktu_keluar->format('Y-m-d'));

        $labels = [];
        $data   = [];

        for ($i = 0; $i < 7; $i++) {
            $date     = $start->copy()->addDays($i);
            $key      = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $data[]   = $rows->has($key) ? $rows[$key]->sum('biaya_total') : 0;
        }

        return [
            'datasets' => [
                [
                    'label'       => 'Pendapatan (Rp)',
                    'data'        => $data,
                    'borderColor' => '#10b981',
                    'fill'        => false,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
