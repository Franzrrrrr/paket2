<?php

namespace App\Filament\Resources\AreaParkirs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AreaPakirForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_area')
                    ->required()
                    ->maxLength(255),
                TextInput::make('kapasitas')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('terisi')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ]);
    }
}
