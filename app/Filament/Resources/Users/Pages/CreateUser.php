<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    function afterCreate(): void
    {
        \App\Models\LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Membuat pengguna baru dengan nama ' . $this->record->name,
        ]);
    }
}
