<?php

namespace App\Filament\Resources\Tarifs\Pages;

use App\Filament\Resources\Tarifs\TarifResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTarif extends CreateRecord
{
    protected static string $resource = TarifResource::class;

    function afterCreate(): void
    {
        \App\Models\LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Membuat tarif baru dengan nama ' . $this->record->name,
        ]);
    }

    protected function getCreatedNotificationMessage(): ?string
    {
        return 'Tarif telah dibuat!';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
