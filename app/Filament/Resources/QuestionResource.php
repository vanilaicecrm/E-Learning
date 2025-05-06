<?php

namespace App\Filament\Resources;

use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use App\Filament\Resources\QuestionResource\Pages;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Radio::make('input_method')
                ->label('Metode Input')
                ->options([
                    'manual' => 'Input Manual',
                    'upload' => 'Upload Excel',
                ])
                ->default('manual')
                ->reactive(),

            Section::make('Input Manual')
                ->schema([
                    TextInput::make('question')
                        ->label('Pertanyaan')
                        ->required(),

                    Textarea::make('explanation')
                        ->label('Penjelasan'),

                    Repeater::make('options')
                        ->label('Pilihan Jawaban')
                        ->relationship('options')
                        ->schema([
                            TextInput::make('option_text')->label('Jawaban')->required(),
                            TextInput::make('score')->label('Skor')->numeric()->required(),
                        ])
                        ->minItems(1),
                ])
                ->visible(fn ($get) => $get('input_method') === 'manual'),

            Section::make('Upload Excel')
                ->schema([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->disk('public')
                        ->directory('uploads/questions')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ]),
                ])
                ->visible(fn ($get) => $get('input_method') === 'upload'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id'),
            Tables\Columns\TextColumn::make('question')->label('Pertanyaan')->limit(50),
            Tables\Columns\TextColumn::make('explanation')->label('Penjelasan')->limit(50),
            Tables\Columns\TextColumn::make('options_count')->label('Jumlah Opsi')->counts('options'),
            Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime(),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
