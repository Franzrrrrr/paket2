<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Transaksi;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Actions\Action;
use Carbon\Carbon;

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
            ])
            ->actions([
                Action::make('Checkout')
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->visible(fn ($record) => is_null($record->waktu_keluar))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->waktu_keluar = now();
                        $diffInHours = ceil($record->waktu_masuk->diffInHours($record->waktu_keluar)) ?: 1;
                        
                        $tarif_per_jam = $record->tarif->tarif_per_jam ?? 0;
                        
                        // Cek inap
                        $diffInDays = $record->waktu_masuk->startOfDay()->diffInDays($record->waktu_keluar->startOfDay());
                        $denda_inap = $record->tarif->denda_inap_per_hari ?? 0;

                        $biaya_parkir = $diffInHours * $tarif_per_jam;
                        $biaya_inap = $diffInDays * $denda_inap;

                        $record->durasi_jam = $diffInHours;
                        $record->biaya_total = $biaya_parkir + $biaya_inap;
                        $record->status = 'selesai';
                        $record->save();
                    }),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
