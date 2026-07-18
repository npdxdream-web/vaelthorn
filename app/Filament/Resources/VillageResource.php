<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VillageResource\Pages;
use App\Models\City;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VillageResource extends Resource
{
    protected static ?string $model = Village::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationLabel = 'หมู่บ้าน';
    protected static ?string $navigationGroup = 'โลก';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ข้อมูลพื้นฐาน')->schema([
                Forms\Components\Select::make('city_id')
                    ->label('เมือง')
                    ->options(fn () => City::orderBy('name')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('name')
                    ->label('ชื่อหมู่บ้าน')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('รายละเอียด')
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Zone Settings')->schema([
                Forms\Components\Toggle::make('is_training_zone')
                    ->label('Training Zone')
                    ->helperText('เปิดเพื่อให้ Level 0 เขียนได้ (Onboarding Stage A) — จะปิด require_approval อัตโนมัติ')
                    ->live(),
                Forms\Components\Toggle::make('require_approval')
                    ->label('ต้องอนุมัติก่อนโพสต์ขึ้น')
                    ->helperText('ไม่สามารถตั้งบน Training Zone — จะมีผลเต็มรูปแบบในเฟส Live Post')
                    ->disabled(fn (Forms\Get $get) => (bool) $get('is_training_zone')),
            ])->columns(2),

            Forms\Components\Section::make('Write Restrictions — ใครเขียนได้')->schema([
                Forms\Components\TextInput::make('write_min_level')
                    ->label('Level ขั้นต่ำ')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->helperText('0 = ไม่จำกัด'),
                Forms\Components\Select::make('write_min_role')
                    ->label('Role ขั้นต่ำ')
                    ->options([
                        'player'    => 'ทุกคน (Player+)',
                        'moderator' => 'Moderator+',
                        'admin'     => 'Admin+',
                    ])
                    ->placeholder('ทุกคน (ไม่จำกัด)')
                    ->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Read Restrictions — Groundwork (ยังไม่มีผล)')->schema([
                Forms\Components\TextInput::make('read_min_level')
                    ->label('Level ขั้นต่ำสำหรับอ่าน')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->disabled()
                    ->helperText('จะมีผลในเฟสถัดไป'),
                Forms\Components\Select::make('read_min_role')
                    ->label('Role ขั้นต่ำสำหรับอ่าน')
                    ->options([
                        'player'    => 'ทุกคน (Player+)',
                        'moderator' => 'Moderator+',
                        'admin'     => 'Admin+',
                    ])
                    ->placeholder('ทุกคน (ไม่จำกัด)')
                    ->disabled()
                    ->helperText('จะมีผลในเฟสถัดไป')
                    ->nullable(),
            ])->columns(2)->collapsible()->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('city.name')
                    ->label('เมือง')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('หมู่บ้าน')
                    ->searchable(),
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
            'index'  => Pages\ListVillages::route('/'),
            'create' => Pages\CreateVillage::route('/create'),
            'edit'   => Pages\EditVillage::route('/{record}/edit'),
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
