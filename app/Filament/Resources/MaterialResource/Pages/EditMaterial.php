<?php

namespace App\Filament\Resources\MaterialResource\Pages;

use App\Filament\Resources\MaterialResource;
use Filament\Resources\Pages\EditRecord;
use App\Services\GeminiService;

class EditMaterial extends EditRecord
{
    protected static string $resource = MaterialResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['ai_summary_enabled']) && !empty($data['description'])) {
            $summary = app(GeminiService::class)->ringkas("Ringkas materi berikut:\n\n" . $data['description']);
            $data['ringkasan'] = $summary ?? 'Ringkasan gagal dibuat.';
        }

        return $data;
    }
}
