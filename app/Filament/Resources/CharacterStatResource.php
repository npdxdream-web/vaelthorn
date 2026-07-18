<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CharacterStatResource\Pages;
use App\Filament\Resources\CharacterStatResource\RelationManagers;
use App\Models\CharacterStat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CharacterStatResource extends Resource
{
    protected static ?string $model = CharacterStat::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Stats';
    protected static ?string $navigationGroup = 'ตัวละคร';
    protected static ?int $navigationSort = 2;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('character_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('level')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('hp')
                    ->required()
                    ->numeric()
                    ->default(100),
                Forms\Components\TextInput::make('mana')
                    ->required()
                    ->numeric()
                    ->default(50),
                Forms\Components\TextInput::make('agi')
                    ->required()
                    ->numeric()
                    ->default(10),
                Forms\Components\TextInput::make('str')
                    ->required()
                    ->numeric()
                    ->default(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('character_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hp')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mana')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('str')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCharacterStats::route('/'),
            'create' => Pages\CreateCharacterStat::route('/create'),
            'edit'   => Pages\EditCharacterStat::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }
}
