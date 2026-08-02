<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoticeBoardResource\Pages;
use App\Models\NoticeBoard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NoticeBoardResource extends Resource
{
    protected static ?string $model = NoticeBoard::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'ป้ายประกาศ';
    protected static ?string $navigationGroup = 'จัดการเนื้อหา';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('color')
                    ->required()
                    ->maxLength(255)
                    ->default('#c8a84b'),
                Forms\Components\TextInput::make('icon')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('เปิดใช้งาน')
                    ->helperText('ปิดแล้วจะไม่แสดงในหน้าแรกของเว็บ')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('color')
                    ->searchable(),
                Tables\Columns\TextColumn::make('icon')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('เปิดใช้งาน')
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
            'index'  => Pages\ListNoticeBoards::route('/'),
            'create' => Pages\CreateNoticeBoard::route('/create'),
            'edit'   => Pages\EditNoticeBoard::route('/{record}/edit'),
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
