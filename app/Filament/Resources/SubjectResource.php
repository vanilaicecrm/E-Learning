<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Major;
use App\Models\Grade;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Curriculum Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Subject Name')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->nullable()
                    ->label('Description'),
                Forms\Components\Select::make('major_id')
                    ->label('Major')
                    ->options(Major::all()->pluck('name', 'id'))
                    ->nullable(),
                Forms\Components\Select::make('grade_id')
                    ->label('Grade')
                    ->options(Grade::all()->pluck('name', 'id'))
                    ->nullable(),
                Forms\Components\Toggle::make('ai_summary_enabled')
                    ->label('Enable AI Summary')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Subject Name'),
                Tables\Columns\TextColumn::make('major.name')
                    ->label('Major'),
                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Grade'),
                Tables\Columns\BooleanColumn::make('ai_summary_enabled')
                    ->label('AI Summary Enabled'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('major')
                    ->options(Major::all()->pluck('name', 'id'))
                    ->label('Major'),
                Tables\Filters\SelectFilter::make('grade')
                    ->options(Grade::all()->pluck('name', 'id'))
                    ->label('Grade'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add any relation managers here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
