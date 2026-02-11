<?php

namespace App\Filament\Resources\Tarifs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TarifForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('jenis_kendaraan')
                    ->options(['motor' => 'Motor', 'mobil' => 'Mobil', 'lainnya' => 'Lainnya'])
                    ->required(),
                TextInput::make('tarif_per_jam')
                    ->required()
                    ->numeric(),
            ]);
    }
}
