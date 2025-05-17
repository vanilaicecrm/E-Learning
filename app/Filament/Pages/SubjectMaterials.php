<?php

namespace App\Filament\Pages;

use App\Models\Subject;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class SubjectMaterials extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static string $view = 'filament.pages.subject-materials';
    protected static ?string $title = 'Materi per Mata Pelajaran';
    protected static ?string $navigationGroup = 'Learning';

    public $subjectsWithMaterials;

    public function mount()
    {
        // Ambil data mata pelajaran dengan materi yang terkait
        $this->subjectsWithMaterials = Subject::with('materials')->get();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return !Auth::user()?->hasRole('admin'); // hanya untuk role non-admin
    }
}
