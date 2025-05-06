<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Material Name')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->required()
                ->maxLength(500),

            Forms\Components\FileUpload::make('file')
                ->label('Material File')
                ->disk('public')
                ->directory('materials')
                ->required(),

            Forms\Components\Select::make('major_id')
                ->label('Major')
                ->relationship('major', 'name')
                ->required(),

            Forms\Components\Select::make('grade_id')
                ->label('Grade')
                ->relationship('grade', 'name')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Material Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('major.name')
                    ->label('Major')
                    ->sortable(),

                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Grade')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
{
    // Jika tidak dalam request HTTP (misal saat artisan), langsung kembalikan default query
    if (!app()->runningInConsole() && request()?->user()) {
        $user = request()->user();

        if (method_exists($user, 'hasRole') && $user->hasRole('siswa')) {
            return parent::getEloquentQuery()
                ->where('major_id', $user->major_id)
                ->where('grade_id', $user->grade_id);
        }
    }

    // Default query (admin atau role lain)
    return parent::getEloquentQuery();
}

    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}
