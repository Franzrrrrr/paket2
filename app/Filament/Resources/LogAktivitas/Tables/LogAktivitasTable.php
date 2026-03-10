<?php

namespace App\Filament\Resources\LogAktivitas\Tables;

use App\Filament\Exports\LogAktivitasExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction as ActionsExportBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogAktivitasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('aktivitas')
                    ->label('Aktivitas')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('waktu_aktivitas')
                    ->label('Waktu Aktivitas')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ExportAction::make('log_aktivitas')
                    ->label('Ekspor Log')
                    ->exporter(LogAktivitasExporter::class)
                    ->formats([
                        ExportFormat::Csv,
                        ExportFormat::Xlsx,
                    ])
                    ->modifyQueryUsing(
                        fn(\Illuminate\Database\Eloquent\Builder $query, $livewire)
                            => $livewire->getFilteredSortedTableQuery()
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    ActionsExportBulkAction::make()
                        ->exporter(LogAktivitasExporter::class)
                        ->formats([
                            ExportFormat::Csv,
                            ExportFormat::Xlsx,
                        ]),
                ]),
            ]);
    }
}
