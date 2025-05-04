<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Ujian extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-pencil-alt';

    protected static string $view = 'filament.pages.ujian';

    public $packageId;

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Tidak tampil di sidebar
    }

    public function mount($packageId)
    {
        $this->packageId = $packageId;
    }
}

