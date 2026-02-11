<?php

namespace App\Filament\Resources\Tarifs;

use App\Filament\Resources\Tarifs\Pages\CreateTarif;
use App\Filament\Resources\Tarifs\Pages\EditTarif;
use App\Filament\Resources\Tarifs\Pages\ListTarifs;
use App\Filament\Resources\Tarifs\Schemas\TarifForm;
use App\Filament\Resources\Tarifs\Tables\TarifsTable;
use App\Models\Tarif;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TarifResource extends Resource
{
    protected static ?string $model = Tarif::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TarifForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TarifsTable::configure($table);
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
            'index' => ListTarifs::route('/'),
            'create' => CreateTarif::route('/create'),
            'edit' => EditTarif::route('/{record}/edit'),
        ];
    }
}
