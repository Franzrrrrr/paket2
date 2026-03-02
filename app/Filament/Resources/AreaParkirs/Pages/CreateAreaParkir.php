<?php

namespace App\Filament\Resources\AreaParkirs\Pages;

use App\Filament\Resources\AreaParkirs\AreaPakirResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAreaParkir extends CreateRecord
{
    protected static string $resource = AreaPakirResource::class;

    function afterCreate(): void
    {
        \App\Models\LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Membuat area parkir baru',
        ]);
    }
}
