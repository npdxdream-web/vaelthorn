<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CraftingRecipeResource\Pages;
use App\Models\CraftingRecipe;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CraftingRecipeResource extends Resource
{
    protected static ?string $model = CraftingRecipe::class;

    protected static ?string $navigationIcon  = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'สูตรคราฟต์ (Shop/Blacksmith)';
    protected static ?string $navigationGroup = 'เศรษฐกิจ';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ข้อมูลสูตร')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('ชื่อสูตร')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('category')
                        ->label('หมวด')
                        ->options([
                            'shop'       => 'Shop (ซื้อทันที)',
                            'blacksmith' => 'Blacksmith (หลอมร่วมกัน)',
                        ])
                        ->required()
                        ->live(),
                    Forms\Components\Select::make('result_item_id')
                        ->label('ไอเทมที่ได้')
                        ->relationship('resultItem', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('result_quantity')
                        ->label('จำนวนที่ได้')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required(),
                    Forms\Components\TextInput::make('gold_cost')
                        ->label('ราคา (Gold) — ทางเลือกซื้อด้วยเงิน')
                        ->numeric()
                        ->minValue(0)
                        ->nullable()
                        ->helperText('ปล่อยว่างถ้าไม่ต้องการให้ซื้อด้วยเงินได้')
                        ->visible(fn (Get $get) => $get('category') === 'shop'),
                    Forms\Components\TextInput::make('craft_duration_minutes')
                        ->label('เวลาหลอม (นาที)')
                        ->numeric()
                        ->minValue(1)
                        ->required(fn (Get $get) => $get('category') === 'blacksmith')
                        ->visible(fn (Get $get) => $get('category') === 'blacksmith'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('เปิดใช้งาน')
                        ->default(true),
                ]),

            Forms\Components\Section::make('วัตถุดิบที่ต้องใช้')
                ->schema([
                    Forms\Components\Repeater::make('materials')
                        ->relationship('materials')
                        ->label('')
                        ->schema([
                            Forms\Components\Select::make('material_item_id')
                                ->label('ไอเทม')
                                ->options(fn () => Item::orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                            Forms\Components\TextInput::make('quantity_required')
                                ->label('จำนวนที่ต้องใช้')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('เพิ่มวัตถุดิบ')
                        ->minItems(1)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อสูตร')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('category')
                    ->label('หมวด')
                    ->colors([
                        'success' => 'shop',
                        'warning' => 'blacksmith',
                    ]),
                Tables\Columns\TextColumn::make('resultItem.name')
                    ->label('ไอเทมที่ได้'),
                Tables\Columns\TextColumn::make('gold_cost')
                    ->label('ราคา Gold')
                    ->default('—'),
                Tables\Columns\TextColumn::make('craft_duration_minutes')
                    ->label('เวลาหลอม (นาที)')
                    ->default('—'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('เปิดใช้')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'shop'       => 'Shop',
                        'blacksmith' => 'Blacksmith',
                    ]),
            ])
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
            'index'  => Pages\ListCraftingRecipes::route('/'),
            'create' => Pages\CreateCraftingRecipe::route('/create'),
            'edit'   => Pages\EditCraftingRecipe::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
}
