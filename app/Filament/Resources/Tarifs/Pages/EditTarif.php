<?php

namespace App\Filament\Resources\Tarifs\Pages;

use App\Filament\Resources\Tarifs\TarifResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTarif extends EditRecord
{
    protected static string $resource = TarifResource::class;

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
            'aktivitas' => 'Mengedit tarif dengan nama ' . $this->record->name,
        ]);
    }
}
