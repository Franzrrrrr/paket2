<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\CreateActivityLog;
use App\Filament\Resources\ActivityLogs\Pages\EditActivityLog;
use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\LogAktivitas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = LogAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Activity Logs';

    protected static ?string $modelLabel = 'Activity Log';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Form components can be added here if needed
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('aktivitas')
                    ->label('Activity')
                    ->searchable()
                    ->wrap(),

                \Filament\Tables\Columns\TextColumn::make('activity_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'primary' => 'booking',
                        'success' => 'exit',
                        'warning' => 'system',
                        'danger' => 'cleanup',
                        'info' => 'admin_action',
                    ]),

                \Filament\Tables\Columns\TextColumn::make('log_level')
                    ->label('Level')
                    ->badge()
                    ->colors([
                        'gray' => 'debug',
                        'success' => 'info',
                        'warning' => 'warning',
                        'danger' => 'error',
                        'red' => 'critical',
                    ]),

                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->placeholder('System'),

                \Filament\Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->sortable()
                    ->toggleable(),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('activity_type')
                    ->options([
                        'booking' => 'Booking',
                        'exit' => 'Exit',
                        'payment' => 'Payment',
                        'system' => 'System',
                        'cleanup' => 'Cleanup',
                        'admin_action' => 'Admin Action',
                        'authentication' => 'Authentication',
                    ]),

                \Filament\Tables\Filters\SelectFilter::make('log_level')
                    ->options([
                        'debug' => 'Debug',
                        'info' => 'Info',
                        'warning' => 'Warning',
                        'error' => 'Error',
                        'critical' => 'Critical',
                    ]),

                \Filament\Tables\Filters\Filter::make('recent')
                    ->query(fn($query) => $query->where('created_at', '>=', now()->subHours(24)))
                    ->label('Last 24 Hours'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
            'create' => CreateActivityLog::route('/create'),
            'edit' => EditActivityLog::route('/{record}/edit'),
        ];
    }
}
