<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'เศรษฐกิจ';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('rarity')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('bonus_str')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('bonus_agi')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('bonus_int')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('bonus_hp')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('bonus_mana')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_tradeable')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('rarity'),
                Tables\Columns\TextColumn::make('bonus_str')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bonus_agi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bonus_int')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bonus_hp')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bonus_mana')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_tradeable')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
            'index'  => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit'   => Pages\EditItem::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->isSuperAdmin() ?? false; }
}
