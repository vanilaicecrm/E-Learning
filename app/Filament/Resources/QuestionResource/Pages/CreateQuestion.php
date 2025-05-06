<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionsImport;
use Filament\Notifications\Notification;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Jika metode upload, tidak menyimpan record kosong
        if ($data['input_method'] === 'upload') {
            return [];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $state = $this->form->getState();

        if ($state['input_method'] === 'upload' && isset($state['file'])) {
            $filePath = Storage::disk('public')->path($state['file']);
            Excel::import(new QuestionsImport, $filePath);

            Notification::make()
                ->title('Berhasil')
                ->body('Soal berhasil diimpor dari Excel.')
                ->success()
                ->send();

            $this->redirect(QuestionResource::getUrl('index'));
        }
    }
}
