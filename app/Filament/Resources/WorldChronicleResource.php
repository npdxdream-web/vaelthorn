<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorldChronicleResource\Pages;
use App\Models\Event;
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
            Forms\Components\Select::make('event_id')
                ->label('Related Event')
                ->options(Event::orderByDesc('created_at')->pluck('title', 'id'))
                ->searchable()
                ->nullable(),

            Forms\Components\DateTimePicker::make('generated_at')
                ->label('Record Date')
                ->default(now())
                ->required(),

            Forms\Components\Toggle::make('is_published')
                ->label('Published')
                ->default(false)
                ->helperText('เปิดเพื่อให้ Player เห็นใน /chronicles'),

            Forms\Components\Textarea::make('content')
                ->label('Chronicle Content')
                ->required()
                ->rows(20)
                ->columnSpanFull()
                ->helperText('เนื้อหาประวัติศาสตร์ที่ AI หรือ Admin เขียนขึ้น'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('event.city.name')
                    ->label('City'),
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
