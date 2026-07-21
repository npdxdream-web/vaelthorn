<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TravelPermitResource\Pages;
use App\Models\TravelPermit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class TravelPermitResource extends Resource
{
    protected static ?string $model = TravelPermit::class;

    protected static ?string $navigationIcon  = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'ใบอนุญาตข้ามเขต';
    protected static ?string $navigationGroup = 'สมาชิก';
    protected static ?int    $navigationSort  = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ออกใบอนุญาต')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('item_name')
                        ->label('ชื่อไอเทม')
                        ->required()
                        ->maxLength(255)
                        ->visibleOn('create'),
                    Forms\Components\Select::make('character_id')
                        ->label('มอบให้ใคร')
                        ->relationship('character', 'name')
                        ->searchable()
                        ->required()
                        ->disabledOn('edit'),
                    Forms\Components\Select::make('kingdom_id')
                        ->label('Kingdom ปลายทาง')
                        ->relationship('kingdom', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('valid_days')
                        ->label('จำนวนวันที่ใช้งานได้ (นับจากวันที่กดใช้งาน)')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ]),

            Forms\Components\Section::make('สถานะการใช้งาน')
                ->visibleOn('edit')
                ->schema([
                    Forms\Components\Placeholder::make('status')
                        ->label('สถานะ')
                        ->content(function (?TravelPermit $record) {
                            if (! $record || ! $record->activated_at) {
                                return new HtmlString('<span style="color:#c8a84b">ยังไม่ได้ใช้งาน</span>');
                            }

                            $color = $record->isActive() ? '#4ade80' : '#f87171';
                            return new HtmlString(
                                "<span style='color:{$color}'>หมดอายุวันที่ {$record->expires_at->format('d M Y H:i')}</span>"
                            );
                        }),
                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('แก้ไขวันหมดอายุ')
                        ->helperText('แก้ไขได้เฉพาะเมื่อผู้เล่นใช้งานใบอนุญาตแล้ว'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.name')
                    ->label('ไอเทม')
                    ->searchable(),
                Tables\Columns\TextColumn::make('character.name')
                    ->label('มอบให้')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kingdom.name')
                    ->label('Kingdom ปลายทาง'),
                Tables\Columns\TextColumn::make('valid_days')
                    ->label('อายุ (วัน)'),
                Tables\Columns\TextColumn::make('activated_at')
                    ->label('ใช้งานเมื่อ')
                    ->dateTime()
                    ->default('ยังไม่ได้ใช้งาน'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('หมดอายุ')
                    ->dateTime()
                    ->default('—'),
                Tables\Columns\TextColumn::make('grantedBy.name')
                    ->label('ออกโดย'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTravelPermits::route('/'),
            'create' => Pages\CreateTravelPermit::route('/create'),
            'edit'   => Pages\EditTravelPermit::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
}
