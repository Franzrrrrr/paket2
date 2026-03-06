<?php

namespace App\Filament\Pages\Transaksi;

use App\Models\Transaksi;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Routing\Route;

class View extends Page
{
    protected string $view = 'filament.pages.transaksi.view';
    protected static ?string $slug = 'transaksi/{record}/detail';
    protected static bool $shouldRegisterNavigation = false;
    
    public Transaksi $record;

    public function mount(int|string $record): void
    {
        $this->record = Transaksi::findOrFail($record);
    }

    public static function getRouteName(?Panel $panel = null): string
    {
        return 'filament.admin.pages.transaksi.{record}.detail';
    }
}