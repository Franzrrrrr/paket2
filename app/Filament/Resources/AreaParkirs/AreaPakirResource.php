<?php

namespace App\Filament\Resources\AreaParkirs;

use App\Filament\Resources\AreaParkirs\Pages\CreateAreaParkir;
use App\Filament\Resources\AreaParkirs\Pages\EditAreaParkir;
use App\Filament\Resources\AreaParkirs\Pages\ListAreaParkirs;
use App\Filament\Resources\AreaParkirs\Schemas\AreaPakirForm;
use App\Filament\Resources\AreaParkirs\Tables\AreaParkirsTable;
use App\Models\AreaParkir;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AreaPakirResource extends Resource
{
    protected static ?string $model = AreaParkir::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Area Parkir';

    protected static ?string $modelLabel = 'Area Parkir';

    public static function form(Schema $schema): Schema
    {
        return AreaPakirForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AreaParkirsTable::configure($table);
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
            'index' => ListAreaParkirs::route('/'),
            'create' => CreateAreaParkir::route('/create'),
            'edit' => EditAreaParkir::route('/{record}/edit'),
        ];
    }
}
