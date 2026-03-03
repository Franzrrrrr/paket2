<?php

namespace App\Filament\Widgets;

use App\Models\LogAktivitas;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityLogs extends BaseWidget
{
    protected static ?string $heading = 'Log Aktivitas Terbaru';
    protected static ?int $sort = 7;

    protected  ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LogAktivitas::with('user')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->label('User')
                    ->searchable(),

                Tables\Columns\TextColumn::make('aktivitas')
                    ->label('Aktivitas')
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
            ]);
    }
}