<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Transaksi;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class TransaksiPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.transaksi-page';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaksi::query())
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('kendaraan.plat_nomor')
                    ->label('Plat Nomor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('waktu_masuk')
                    ->label('Waktu Masuk')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                TextColumn::make('waktu_keluar')
                    ->label('Waktu Keluar')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                TextColumn::make('durasi_jam')
                    ->label('Durasi (Jam)')
                    // ->numeric(decimals: 1)
                    ->sortable(),
                TextColumn::make('biaya_total')
                    ->label('Biaya Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'warning',
                        'selesai' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
