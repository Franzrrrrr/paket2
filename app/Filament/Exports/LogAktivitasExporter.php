<?php

namespace App\Filament\Exports;

use App\Models\LogAktivitas;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class LogAktivitasExporter extends Exporter
{
    protected static ?string $model = LogAktivitas::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),

            ExportColumn::make('user.name')
                ->label('Pengguna'),

            ExportColumn::make('aktivitas')
                ->label('Aktivitas'),

            ExportColumn::make('waktu_aktivitas')
                ->label('Waktu Aktivitas'),

            ExportColumn::make('created_at')
                ->label('Dibuat Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = number_format($export->successful_rows);

        return "Export log aktivitas selesai. {$count} baris berhasil diekspor.";
    }
}
