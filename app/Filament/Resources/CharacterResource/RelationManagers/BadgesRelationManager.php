<?php

namespace App\Filament\Resources\CharacterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BadgesRelationManager extends RelationManager
{
    protected static string $relationship = 'badges';
    protected static ?string $title = 'Badges';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('badge_id')
                ->label('Badge')
                ->relationship('badge', 'name')
                ->searchable()
                ->required(),
            Forms\Components\DateTimePicker::make('acquired_at')
                ->label('ได้รับเมื่อ')
                ->default(now())
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('badge.icon')
                    ->label(''),
                Tables\Columns\TextColumn::make('badge.name')
                    ->label('Badge')
                    ->searchable(),
                Tables\Columns\TextColumn::make('badge.description')
                    ->label('คำอธิบาย')
                    ->limit(50),
                Tables\Columns\TextColumn::make('acquired_at')
                    ->label('ได้รับเมื่อ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('acquired_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('มอบ Badge'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('แก้ไข'),
                Tables\Actions\DeleteAction::make()->label('ถอน Badge'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('ถอนที่เลือก'),
                ]),
            ]);
    }
}
