<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadgeResource\Pages;
use App\Models\Badge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BadgeResource extends Resource
{
    protected static ?string $model = Badge::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Progression';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('icon')
                ->placeholder('⭐ หรือ URL รูป')
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->maxLength(500)
                ->columnSpanFull(),

            Forms\Components\Select::make('condition_type')
                ->options([
                    'posts'        => 'จำนวน Post ที่ approved',
                    'events'       => 'จำนวน Event ที่เข้าร่วม',
                    'manual'       => 'มอบด้วยตนเอง (Manual)',
                    'first_post'   => 'โพสต์แรก',
                    'first_event'  => 'Event แรก',
                ])
                ->placeholder('เลือกเงื่อนไข'),

            Forms\Components\TextInput::make('condition_value')
                ->numeric()
                ->default(0)
                ->helperText('ค่าที่ต้องถึง เช่น ต้องมี 10 posts'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('')
                    ->width(40),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('condition_type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'manual'      => 'warning',
                        'posts'       => 'success',
                        'events'      => 'info',
                        default       => 'gray',
                    }),
                Tables\Columns\TextColumn::make('condition_value')
                    ->label('Threshold')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('characterBadges_count')
                    ->label('Awarded')
                    ->counts('characterBadges')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
            'index'  => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'edit'   => Pages\EditBadge::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->isSuperAdmin() ?? false; }
}
