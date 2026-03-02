<?php

namespace App\Filament\Resources\AreaParkirs\Pages;

use App\Filament\Resources\AreaParkirs\AreaPakirResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAreaParkir extends EditRecord
{
    protected static string $resource = AreaPakirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    function afterSave(): void
    {
        \App\Models\LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Mengedit area parkir dengan ID ' . $this->record->name,
        ]);
    }
}
