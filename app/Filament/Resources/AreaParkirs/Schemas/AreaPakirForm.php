<?php

namespace App\Filament\Resources\AreaParkirs\Schemas;

use EduardoRibeiroDev\FilamentLeaflet\Enums\TileLayer;
use EduardoRibeiroDev\FilamentLeaflet\Fields\MapPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;

class AreaPakirForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ComponentsSection::make('Data Area Parkir')
                    ->schema([
                        TextInput::make('nama_area')
                            ->label('Nama Area')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('kapasitas')
                            ->label('Kapasitas')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('terisi')
                            ->label('Terisi')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(3),

                ComponentsSection::make('Lokasi Area Parkir')
                    ->collapsible()
                    ->schema([
                        Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                ComponentsSection::make('Lokasi Area Parkir')
                    ->description('Klik pada peta untuk menentukan koordinat lokasi.')
                    ->icon('heroicon-o-map-pin')
                    ->collapsible()
                    ->schema([
                        MapPicker::make('location')
                            ->label('Pilih Lokasi di Peta')
                            ->height(400)
                            ->zoom(13)
                            ->autoCenter()
                            ->tileLayersUrl(TileLayer::OpenStreetMap)
                            ->latitudeFieldName('latitude')
                            ->longitudeFieldName('longitude')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
