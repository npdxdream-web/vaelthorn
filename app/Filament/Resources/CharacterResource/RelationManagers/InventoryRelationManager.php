<?php

namespace App\Filament\Resources\CharacterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventory';
    protected static ?string $title = 'ไอเทม';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('item_name')
                ->label('ชื่อไอเทม')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('quantity')
                ->label('จำนวน')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(1),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->label('ชื่อไอเทม')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('จำนวน')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('เพิ่มเมื่อ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('เพิ่มไอเทม'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('แก้ไข'),
                Tables\Actions\DeleteAction::make()->label('ลบ'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('ลบที่เลือก'),
                ]),
            ]);
    }
}
