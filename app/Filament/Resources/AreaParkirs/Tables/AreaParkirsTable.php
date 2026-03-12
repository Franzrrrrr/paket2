<?php

namespace App\Filament\Resources\AreaParkirs\Tables;

use EduardoRibeiroDev\FilamentLeaflet\Support\Markers\Marker;
use EduardoRibeiroDev\FilamentLeaflet\Tables\MapColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AreaParkirsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_area')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('kapasitas')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('terisi')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(40)
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('tarif_summary')
                    ->label('Tarif')
                    ->getStateUsing(function ($record) {
                        $tarifs = $record->tarifs()->get();

                        if ($tarifs->isEmpty()) {
                            return '-';
                        }

                        return $tarifs->unique('jenis_kendaraan')->map(function ($tarif) {
                            $perJam   = 'Rp ' . number_format($tarif->tarif_per_jam,   0, ',', '.');
                            $perMenit = 'Rp ' . number_format($tarif->tarif_per_menit, 0, ',', '.');
                            return "{$tarif->jenis_kendaraan}: {$perJam}/jam | {$perMenit}/menit";
                        })->join("\n");
                    })
                    ->wrap()
                    ->placeholder('-'),
                TextColumn::make('latitude')
                    ->label('Koordinat')
                    ->formatStateUsing(fn ($record) =>
                        $record->latitude && $record->longitude
                            ? "{$record->latitude}, {$record->longitude}"
                            : '-'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                MapColumn::make('location')
                    ->label('Lokasi')
                    ->height(72)
                    ->zoom(5)
                    ->pickMarker(fn (Marker $marker) => $marker->iconSize([14, 25]))
                    ->circular(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
