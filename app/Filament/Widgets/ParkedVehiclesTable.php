<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ParkedVehiclesTable extends BaseWidget
{
    protected static ?string $heading = 'Kendaraan Sedang Parkir';
    protected static ?int $sort = 6;

    protected ?string $pollingInterval = '15s';

    protected static ?array $pages = [
        'dashboard',
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaksi::with('kendaraan')
                    ->where(function ($query) {
                        $query->whereNull('waktu_keluar')
                              ->orWhere('status', 'masuk');
                    })
                    ->orderBy('waktu_masuk', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('kendaraan.plat_nomor')
                    ->label('Plat Nomor')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kendaraan.jenis_kendaraan')
                    ->label('Jenis')
                    ->badge(),

                Tables\Columns\TextColumn::make('waktu_masuk')
                    ->label('Waktu Masuk')
                    ->dateTime('d M Y, H:i'),

                Tables\Columns\TextColumn::make('durasi')
                    ->label('Durasi')
                    ->getStateUsing(fn(Transaksi $record): string =>
                        $record->waktu_masuk->diffForHumans(Carbon::now(), true)
                    ),
            ]);
    }
}
