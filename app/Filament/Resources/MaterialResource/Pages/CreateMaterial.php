<?php

namespace App\Filament\Resources\MaterialResource\Pages;

use App\Filament\Resources\MaterialResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\GeminiService;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;

class CreateMaterial extends CreateRecord
{
    protected static string $resource = MaterialResource::class;

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                Textarea::make('description')
                    ->required()
                    ->label('Description'),

                Textarea::make('ringkasan')
                    ->disabled()
                    ->label('Ringkasan (AI)')
                    ->columnSpan('full'),

                Actions::make([
                    Action::make('generateSummary')
                        ->label('Ringkas dengan AI')
                        ->icon('heroicon-o-sparkles')
                        ->color('info')
                        ->action(function (array $data, GeminiService $gemini) {
                            $ringkasan = $gemini->ringkas("Ringkas materi berikut:\n\n" . $data['description']);
                            return ['ringkasan' => $ringkasan ?? 'Gagal menghasilkan ringkasan.'];
                        })
                        ->after(function (Action $action, array $arguments, array $data): void {
                            $this->form->fill([
                                'ringkasan' => $arguments['ringkasan'],
                            ]);
                        }),
                ]),
            ]),
        ];
    }
}