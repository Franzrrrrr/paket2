<?php

namespace App\Filament\Resources\Kendaraans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KendaraanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pemilik User')
                    ->searchable()
                    ->preload()
                    ->options(\App\Models\User::role('owner')->pluck('name', 'id'))
                    ->required(),
                TextInput::make('plat_nomor')
                    ->required(),
                Select::make('jenis_kendaraan')
                    ->options(['motor' => 'Motor', 'mobil' => 'Mobil'])
                    ->default('motor')
                    ->required(),
            ]);
    }
}
