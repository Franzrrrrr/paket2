<?php

namespace App\Filament\Resources\AreaParkirs\Tables;

use EduardoRibeiroDev\FilamentLeaflet\Support\Markers\Marker;
use EduardoRibeiroDev\FilamentLeaflet\Tables\MapColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
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
                TextColumn::make('latitude')
                    ->label('Koordinat')
                    ->formatStateUsing(fn($record) =>
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
                    ->pickMarker(fn(Marker $marker) => $marker->iconSize([14, 25]))
                    ->circular()
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // Tombol Lihat di Peta — hanya muncul jika koordinat tersedia
                // Action::make('lihatPeta')
                //     ->label('Lihat Peta')
                //     ->icon('heroicon-o-map-pin')
                //     ->color('info')
                //     ->url(fn($record) => route('area-parkir.peta', $record->id))
                //     ->openUrlInNewTab()
                //     ->visible(fn($record) => $record->latitude && $record->longitude),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
