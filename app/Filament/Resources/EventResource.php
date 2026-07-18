<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Events';
    protected static ?string $navigationGroup = 'เนื้อเรื่อง';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ข้อมูล Event')->schema([
                Forms\Components\TextInput::make('title')
                    ->label('ชื่อ Event')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('ประเภท')
                    ->required()
                    ->options([
                        'flash'     => 'Flash (2–6 ชม.)',
                        'location'  => 'Location (1–2 สัปดาห์)',
                        'story_arc' => 'Story Arc (1+ เดือน)',
                        'crisis'    => 'Crisis (24–48 ชม.)',
                    ])
                    ->live(),

                Forms\Components\Select::make('status')
                    ->label('สถานะ')
                    ->required()
                    ->options([
                        'draft'    => 'Draft',
                        'active'   => 'Active',
                        'closed'   => 'Closed',
                        'archived' => 'Archived',
                    ])
                    ->default('draft'),

                Forms\Components\Select::make('city_id')
                    ->label('เมือง')
                    ->relationship('city', 'name')
                    ->nullable(),

                Forms\Components\TextInput::make('exp_reward')
                    ->label('EXP ต่อโพสต์')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(15)
                    ->rules(fn (Get $get): array => match ($get('type')) {
                        'flash'     => ['required', 'integer', 'in:1'],
                        default     => ['required', 'integer', 'min:3', 'max:15'],
                    })
                    ->helperText(fn (Get $get): string => match ($get('type')) {
                        'flash'  => 'Flash event ต้องเป็น 1 เสมอ',
                        default  => 'Story Arc / Crisis / Location: 3–15',
                    })
                    ->live(),
            ])->columns(2),

            Forms\Components\Section::make('รายละเอียด')->schema([
                Forms\Components\Textarea::make('description')
                    ->label('คำอธิบาย')
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('start_at')->label('เริ่มต้น'),
                Forms\Components\DateTimePicker::make('end_at')->label('สิ้นสุด'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('ชื่อ Event')
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('ประเภท')
                    ->colors([
                        'warning' => 'flash',
                        'danger'  => 'crisis',
                        'primary' => 'story_arc',
                        'success' => 'location',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('สถานะ')
                    ->colors([
                        'gray'    => 'draft',
                        'success' => 'active',
                        'warning' => 'closed',
                        'danger'  => 'archived',
                    ]),

                Tables\Columns\TextColumn::make('exp_reward')
                    ->label('EXP/โพสต์')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('city.name')
                    ->label('เมือง')
                    ->default('—'),

                Tables\Columns\TextColumn::make('start_at')
                    ->label('เริ่ม')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_at')
                    ->label('สิ้นสุด')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'flash'     => 'Flash',
                        'location'  => 'Location',
                        'story_arc' => 'Story Arc',
                        'crisis'    => 'Crisis',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'    => 'Draft',
                        'active'   => 'Active',
                        'closed'   => 'Closed',
                        'archived' => 'Archived',
                    ]),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit'   => Pages\EditEvent::route('/{record}/edit'),
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
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }
}
