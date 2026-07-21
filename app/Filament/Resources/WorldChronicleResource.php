<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorldChronicleResource\Pages;
use App\Models\WorldChronicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorldChronicleResource extends Resource
{
    protected static ?string $model = WorldChronicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'World';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Chronicles';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label('หัวเรื่อง')
                ->maxLength(255)
                ->required(),

            Forms\Components\Select::make('category')
                ->label('หมวดหมู่')
                ->options([
                    'Lore'      => 'Lore',
                    'History'   => 'Event History',
                    'War'       => 'War',
                    'Political' => 'Political',
                    'Other'     => 'Other',
                ])
                ->searchable()
                ->nullable(),

            Forms\Components\Select::make('kingdom_id')
                ->label('อาณาจักร')
                ->relationship('kingdom', 'name')
                ->searchable()
                ->nullable()
                ->helperText('กำหนดสี/ไอคอนพื้นหลังของการ์ดเมื่อไม่มีภาพปก — ถ้าเว้นว่างจะใช้ Kingdom จาก Event ที่เชื่อมโยงแทน (ถ้ามี)'),

            Forms\Components\FileUpload::make('cover_image')
                ->label('ภาพปก')
                ->image()
                ->disk('public')
                ->directory('chronicles')
                ->imagePreviewHeight('220')
                ->maxSize(5120)
                ->helperText('ถ้าไม่อัปโหลด การ์ดจะใช้ gradient สีอาณาจักรแทน')
                ->columnSpanFull(),

            Forms\Components\Toggle::make('is_published')
                ->label('Published')
                ->default(false)
                ->helperText('เปิดเพื่อให้ Player เห็นใน /chronicles'),

            Forms\Components\Textarea::make('content')
                ->label('Chronicle Content')
                ->required()
                ->rows(20)
                ->columnSpanFull()
                ->helperText('เนื้อหาประวัติศาสตร์ที่ Admin เขียนขึ้น'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('ภาพปก')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(fn () => null),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('title')
                    ->label('หัวเรื่อง')
                    ->searchable()
                    ->default('—')
                    ->limit(40),
                Tables\Columns\TextColumn::make('category')
                    ->label('หมวดหมู่')
                    ->badge()
                    ->default('—'),
                Tables\Columns\TextColumn::make('kingdom.name')
                    ->label('อาณาจักร')
                    ->default('—'),
                Tables\Columns\TextColumn::make('content')
                    ->limit(80)
                    ->label('Preview'),
                Tables\Columns\TextColumn::make('generated_at')
                    ->label('Date')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('generated_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
                Tables\Filters\SelectFilter::make('category')
                    ->label('หมวดหมู่')
                    ->options([
                        'Lore'      => 'Lore',
                        'History'   => 'Event History',
                        'War'       => 'War',
                        'Political' => 'Political',
                        'Other'     => 'Other',
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
            'index'  => Pages\ListWorldChronicles::route('/'),
            'create' => Pages\CreateWorldChronicle::route('/create'),
            'edit'   => Pages\EditWorldChronicle::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canCreate(): bool  { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->isAtLeastAdmin() ?? false; }
}
