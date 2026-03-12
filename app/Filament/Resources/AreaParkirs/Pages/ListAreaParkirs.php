<?php

namespace App\Filament\Resources\AreaParkirs\Pages;

use App\Filament\Resources\AreaParkirs\AreaPakirResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAreaParkirs extends ListRecords
{
    protected static string $resource = AreaPakirResource::class;
    protected ?string $heading = 'Area Parkir';

    public function mount(): void
    {
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Area Parkir'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\AreaParkirWidget::class,
        ];
    }
}
