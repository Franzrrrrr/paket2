<?php

namespace App\Filament\Pages\Transaksi;

use App\Models\Transaksi;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action as ActionsAction;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Pages\Page;

class TransaksiPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.transaksi-page';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Transaksi Page';

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaksi::query()->with(['kendaraan', 'areaParkir', 'tarif']))
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
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('durasi_jam')
                    ->label('Durasi (Jam)')
                    ->suffix(' jam')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('biaya_total')
                    ->label('Biaya Total')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'masuk'      => 'warning',
                        'selesai'    => 'success',
                        'dibatalkan' => 'danger',
                        default      => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Checkout — hanya tampil jika belum keluar
                ActionsAction::make('checkout')
                    ->label('Checkout')
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->visible(fn(Transaksi $record): bool => is_null($record->waktu_keluar))
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Checkout')
                    ->modalDescription(fn(Transaksi $record) => "Checkout kendaraan {$record->kendaraan->plat_nomor}?")
                    ->action(function (Transaksi $record): void {
                        $record->waktu_keluar = now();
                        $diffInHours = ceil($record->waktu_masuk->diffInHours($record->waktu_keluar)) ?: 1;

                        $tarif_per_jam = $record->tarif->tarif_per_jam ?? 0;

                        $diffInDays  = $record->waktu_masuk->startOfDay()->diffInDays($record->waktu_keluar->startOfDay());
                        $denda_inap  = $record->tarif->denda_inap_per_hari ?? 0;

                        $record->durasi_jam  = $diffInHours;
                        $record->biaya_total = ($diffInHours * $tarif_per_jam) + ($diffInDays * $denda_inap);
                        $record->status      = 'selesai';
                        $record->save();
                    }),

                // View Detail — hanya tampil setelah checkout
                ActionsAction::make('view')
                    ->label('Detail')
                    ->button()
                    ->color('info')
                    ->icon('heroicon-o-eye')
                    ->visible(fn(Transaksi $record): bool => !is_null($record->waktu_keluar))
                    ->url(fn(Transaksi $record): string => route(
                        'filament.admin.pages.transaksi.{record}.detail',
                        ['record' => $record->id]
                    )),

                // Cetak Struk — hanya tampil setelah checkout
                ActionsAction::make('cetak')
                    ->label('Cetak')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-printer')
                    ->visible(fn(Transaksi $record): bool => !is_null($record->waktu_keluar))
                    ->url(fn(Transaksi $record): string => route('transaksi.struk', $record->id))
                    ->openUrlInNewTab(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
