<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactions extends BaseWidget
{
    protected static ?string $heading = 'Transaksi Terbaru';
    protected static ?int $sort = 5;

    protected  ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaksi::with('kendaraan', 'areaParkir')
                    ->orderBy('waktu_masuk', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('kendaraan.plat_nomor')
                    ->label('Plat Nomor')
                    ->searchable(),

                Tables\Columns\TextColumn::make('areaParkir.nama_area')
                    ->label('Area Parkir'),

                Tables\Columns\TextColumn::make('waktu_masuk')
                    ->label('Waktu Masuk')
                    ->dateTime('d M Y, H:i'),

                Tables\Columns\TextColumn::make('waktu_keluar')
                    ->label('Waktu Keluar')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('biaya_total')
                    ->label('Biaya')
                    ->money('IDR')
                    ->placeholder('-'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'keluar',
                        'warning' => 'masuk',
                    ]),
            ]);
    }
}