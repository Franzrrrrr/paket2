<?php

namespace App\Filament\Widgets;

use App\Models\AreaParkir;
use App\Models\Transaksi;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;

class NotificationsWidget extends BaseWidget
{
    protected static ?string $heading = 'Notifikasi & Peringatan';
    protected static ?int $sort = 9;

    protected  ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Dummy query base — kita override dengan data virtual
                Transaksi::whereRaw('1 = 0') // empty base, diisi via getTableRecords
            )
            ->columns([
                Tables\Columns\IconColumn::make('type')
                    ->label('')
                    ->icon(fn(string $state): string => match ($state) {
                        'danger'  => 'heroicon-o-x-circle',
                        'warning' => 'heroicon-o-exclamation-triangle',
                        default   => 'heroicon-o-information-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'danger'  => 'danger',
                        'warning' => 'warning',
                        default   => 'info',
                    })
                    ->width('40px'),

                Tables\Columns\TextColumn::make('message')
                    ->label('Pesan')
                    ->wrap(),
            ])
            ->records(fn() => $this->getNotifications())
            ->paginated(false);
    }

    protected function getNotifications(): Collection
    {
        $now      = Carbon::now();
        $messages = collect();

        // Area hampir penuh / penuh
        AreaParkir::where('kapasitas', '>', 0)->get()->each(function ($area) use ($messages) {
            $rate = $area->terisi / $area->kapasitas * 100;

            if ($rate >= 100) {
                $messages->push([
                    'id'      => 'area-full-' . $area->id,
                    'type'    => 'danger',
                    'message' => "Area {$area->nama_area} sudah penuh (100%).",
                ]);
            } elseif ($rate >= 80) {
                $messages->push([
                    'id'      => 'area-warn-' . $area->id,
                    'type'    => 'warning',
                    'message' => "Area {$area->nama_area} hampir penuh (" . round($rate, 1) . "%).",
                ]);
            }
        });

        // Kendaraan parkir anomali > 24 jam
        Transaksi::whereNull('waktu_keluar')
            ->where('waktu_masuk', '<', $now->copy()->subHours(24))
            ->with('kendaraan')
            ->get()
            ->each(function ($trx) use ($messages) {
                $messages->push([
                    'id'      => 'long-park-' . $trx->id,
                    'type'    => 'warning',
                    'message' => "Kendaraan {$trx->kendaraan->plat_nomor} parkir lebih dari 24 jam.",
                ]);
            });

        if ($messages->isEmpty()) {
            $messages->push([
                'id'      => 'ok',
                'type'    => 'info',
                'message' => 'Semua area normal, tidak ada peringatan.',
            ]);
        }

        return $messages;
    }
}