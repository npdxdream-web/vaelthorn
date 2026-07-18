<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardResource\Pages;
use App\Filament\Resources\RewardResource\RelationManagers;
use App\Models\Reward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RewardResource extends Resource
{
    protected static ?string $model = Reward::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('event_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('item_id')
                    ->numeric(),
                Forms\Components\TextInput::make('item_quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('gold_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('exp_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gold_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('exp_amount')
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
            'index'  => Pages\ListRewards::route('/'),
            'create' => Pages\CreateReward::route('/create'),
            'edit'   => Pages\EditReward::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->isSuperAdmin() ?? false; }
}
