<?php

namespace App\Filament\Resources\LogAktivitas;

use App\Filament\Resources\LogAktivitas\Pages\CreateLogAktivitas;
use App\Filament\Resources\LogAktivitas\Pages\EditLogAktivitas;
use App\Filament\Resources\LogAktivitas\Pages\ListLogAktivitas;
use App\Filament\Resources\LogAktivitas\Schemas\LogAktivitasForm;
use App\Filament\Resources\LogAktivitas\Tables\LogAktivitasTable;
use App\Models\LogAktivitas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LogAktivitasResource extends Resource
{
    protected static ?string $model = LogAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LogAktivitasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LogAktivitasTable::configure($table);
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
            'index' => ListLogAktivitas::route('/'),
        ];
    }
}
