<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Columns\IconColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-c-user-circle';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255),

            Forms\Components\DateTimePicker::make('email_verified_at')
                ->label('Verifikasi Email'),

            Forms\Components\Select::make('roles')
                ->label('Peran')
                ->relationship('roles', 'name')
                ->multiple()
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('password')
                ->label('Kata Sandi')
                ->password()
                ->required(fn(string $context): bool => $context === 'create')
                ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                ->dehydrated(fn($state) => filled($state))
                ->maxLength(255),

            Forms\Components\Select::make('major_id')
                ->label('Jurusan')
                ->relationship('major', 'name')
                ->searchable()
                ->preload(),

            Forms\Components\Select::make('grade_id')
                ->label('Kelas')
                ->relationship('grade', 'name')
                ->searchable()
                ->preload(),

            Checkbox::make('can_generate_summary')
                ->label('Akses Ringkasan AI')
                ->default(false)
                ->dehydrated(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Nama')
                ->searchable(),

            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->searchable(),

            IconColumn::make('can_generate_summary')
                ->label('Akses Ringkasan AI')
                ->boolean(),

            Tables\Columns\TextColumn::make('roles.name')
                ->label('Peran')
                ->searchable(),

            Tables\Columns\TextColumn::make('major.name')
                ->label('Jurusan'),

            Tables\Columns\TextColumn::make('grade.name')
                ->label('Kelas'),

            Tables\Columns\TextColumn::make('email_verified_at')
                ->label('Terverifikasi Pada')
                ->dateTime()
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('updated_at')
                ->label('Diperbarui')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            // Tambahkan filter jika diperlukan
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            // Tambahkan relasi jika diperlukan
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
