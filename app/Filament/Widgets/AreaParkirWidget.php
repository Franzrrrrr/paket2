<?php

namespace App\Filament\Widgets;

use App\Models\AreaParkir;
use EduardoRibeiroDev\FilamentLeaflet\Support\Groups\LayerGroup;
use EduardoRibeiroDev\FilamentLeaflet\Widgets\MapWidget;
use EduardoRibeiroDev\FilamentLeaflet\Support\Markers\Marker;

class AreaParkirWidget extends MapWidget
{
    protected static ?int $sort = 10;
    protected ?string $heading = 'Seluruh Area Parkir';

    protected int|string|array $columnSpan = 'full';

    protected int $mapHeight = 480;

    protected function getLayers(): array
    {
        $markers = AreaParkir::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($area) {
                $rate = $area->kapasitas > 0
                    ? round($area->terisi / $area->kapasitas * 100, 1)
                    : 0;

                $sisa  = $area->kapasitas - $area->terisi;

                $color = match (true) {
                    $rate >= 100 => '#ef4444',
                    $rate >= 80  => '#facc15',  
                    default      => '#22c55e',
                };

                $statusLabel = match (true) {
                    $rate >= 100 => '🔴 Penuh',
                    $rate >= 80  => '🟡 Hampir Penuh',
                    default      => '🟢 Tersedia',
                };

                $popup = "
                    <div style='font-family:sans-serif;min-width:200px;padding:4px'>
                        <div style='font-weight:700;font-size:14px;margin-bottom:4px'>{$area->nama_area}</div>
                        <div style='font-size:12px;color:#555;margin-bottom:8px'>{$area->alamat}</div>
                        <table style='width:100%;font-size:12px;border-collapse:collapse'>
                            <tr><td style='padding:2px 0;color:#666'>Kapasitas</td><td style='text-align:right;font-weight:600'>{$area->kapasitas} slot</td></tr>
                            <tr><td style='padding:2px 0;color:#666'>Terisi</td><td style='text-align:right;font-weight:600'>{$area->terisi} slot</td></tr>
                            <tr><td style='padding:2px 0;color:#666'>Tersisa</td><td style='text-align:right;font-weight:600;color:{$color}'>{$sisa} slot</td></tr>
                            <tr><td style='padding:2px 0;color:#666'>Status</td><td style='text-align:right'>{$statusLabel}</td></tr>
                        </table>
                        <div style='background:#e5e7eb;border-radius:999px;height:8px;overflow:hidden;margin-top:8px'>
                            <div style='width:{$rate}%;height:100%;background:{$color};border-radius:999px'></div>
                        </div>
                        <div style='text-align:right;font-size:11px;margin-top:2px;color:#666'>{$rate}% terisi</div>
                    </div>";

                return Marker::make((float) $area->latitude, (float) $area->longitude)
                    ->popup($popup)
                    ->tooltip($area->nama_area);
            })
            ->values() // reset keys
            ->all();   // kembalikan array of Marker objects, BUKAN ->toArray()

        return [
            LayerGroup::make($markers),
        ];
    }
}
