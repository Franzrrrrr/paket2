<?php

namespace App\Filament\Resources\Notifications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data.title')
                    ->label('Title')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('data.message')
                    ->label('Message')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),

                BadgeColumn::make('priority')
                    ->label('Priority')
                    ->colors([
                        'gray' => 'low',
                        'blue' => 'medium',
                        'orange' => 'high',
                        'red' => 'critical',
                    ]),

                BadgeColumn::make('read_at')
                    ->label('Status')
                    ->getStateUsing(fn($record) => $record->read_at ? 'Read' : 'Unread')
                    ->colors([
                        'gray' => 'Read',
                        'success' => 'Unread',
                    ]),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? $state->format('M j, Y g:i A') : 'Never'),
            ])
            ->filters([
                // Filter by priority
                \Filament\Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),

                // Filter by read status
                \Filament\Tables\Filters\Filter::make('unread')
                    ->query(fn(Builder $query): Builder => $query->whereNull('read_at'))
                    ->label('Unread Only'),

                // Filter by type
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'system_alert' => 'System Alert',
                        'booking_activity' => 'Booking Activity',
                        'exit_activity' => 'Exit Activity',
                        'cleanup_report' => 'Cleanup Report',
                        'security_alert' => 'Security Alert',
                        'performance_alert' => 'Performance Alert',
                    ]),
            ])
            ->recordActions([
                Action::make('markAsRead')
                    ->label('Mark as Read')
                    ->icon('heroicon-o-check')
                    ->action(fn($record) => $record->markAsRead())
                    ->visible(fn($record) => !$record->read_at),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('markAsRead')
                        ->label('Mark Selected as Read')
                        ->icon('heroicon-o-check')
                        ->action(function($records) {
                            foreach($records as $record) {
                                if (!$record->read_at) {
                                    $record->markAsRead();
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
