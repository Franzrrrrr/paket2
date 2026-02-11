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
                    ->required()
                    ->options(fn () => \App\Models\User::all()->pluck('name', 'id')),
                TextInput::make('plat_nomor')
                    ->required(),
                Select::make('jenis_kendaraan')
                    ->options(['motor' => 'Motor', 'mobil' => 'Mobil'])
                    ->default('motor')
                    ->required(),
                TextInput::make('warna')
                    ->required(),
                TextInput::make('pemilik')
                    ->required(),
            ]);
    }
}
