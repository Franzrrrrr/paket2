<?php

namespace App\Filament\Resources\AreaParkirs\Pages;

use App\Filament\Resources\AreaParkirs\AreaPakirResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAreaParkir extends CreateRecord
{
    protected static string $resource = AreaPakirResource::class;

    protected function afterCreate(): void
    {

        \App\Models\LogAktivitas::create([
            'user_id' => filament()->auth()->id(),
            'aktivitas' => 'Membuat area parkir baru dengan nama ' . $this->record->nama_area,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationMessage(): ?string
    {
        return 'Area Parkir telah dibuat!';
    }
}
