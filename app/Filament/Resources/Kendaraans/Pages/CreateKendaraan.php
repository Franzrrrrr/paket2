<?php

namespace App\Filament\Resources\Kendaraans\Pages;

use App\Filament\Resources\Kendaraans\KendaraanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKendaraan extends CreateRecord
{
    protected static string $resource = KendaraanResource::class;

        function afterCreate(): void
        {
            \App\Models\LogAktivitas::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'Membuat kendaraan baru dengan nomor ' . $this->record->nomor_kendaraan,
            ]);
        }
}
