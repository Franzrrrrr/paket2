<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    function afterSave(): void
    {
        \App\Models\LogAktivitas::create
        ([
            'user_id' => auth()->id(),
            'aktivitas' => 'Mengedit pengguna dengan nama ' . $this->record->name,
        ]);
    }

    protected function getSavedNotificationMessage(): ?string
    {
        return 'Pengguna telah di ubah!';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
