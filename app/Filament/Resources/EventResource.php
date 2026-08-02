<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\City;
use App\Models\Event;
use App\Models\RewardLog;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

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

                Forms\Components\Placeholder::make('type_color_preview')
                    ->label('สี')
                    ->content(function (Get $get) {
                        $type = $get('type');
                        $meta = Event::typeMeta()[$type] ?? null;

                        if (! $meta) {
                            return new HtmlString('<span class="text-gray-400">—</span>');
                        }

                        return new HtmlString(
                            '<span style="display:inline-flex;align-items:center;gap:6px;">'
                            . '<span style="width:14px;height:14px;border-radius:3px;background:' . $meta['color'] . ';display:inline-block;"></span>'
                            . '<span>' . $meta['icon'] . ' ' . e($meta['label']) . '</span>'
                            . '</span>'
                        );
                    }),

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

                Forms\Components\Select::make('kingdom_id')
                    ->label('อาณาจักร')
                    ->relationship('kingdom', 'name')
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

                Tables\Columns\TextColumn::make('type')
                    ->label('ประเภท')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Event::typeMeta()[$state]['label'] ?? ucfirst($state))
                    ->color(fn (string $state) => Color::hex(Event::typeMeta()[$state]['color'] ?? '#c8a84b')),

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

                Tables\Columns\TextColumn::make('participants_count')
                    ->label('ผู้เข้าร่วม')
                    ->counts('participants')
                    ->sortable(),

                // Not a stored column — computed per-row from reward_logs, since
                // "rewarded" means at least one distinct reward template granted
                // to that character for this Event (see LevelingService::
                // distributeEventRewards's dedup rule). Cheap at this project's
                // scale (~20 players/day, few concurrent Events); would need an
                // eager-loaded subquery instead if that ever changes.
                Tables\Columns\TextColumn::make('reward_stats')
                    ->label('ได้รับ Reward')
                    ->getStateUsing(function (Event $record): string {
                        $participantCount = $record->participants_count ?? $record->participants()->count();
                        $rewardedCount    = RewardLog::where('event_id', $record->id)
                            ->whereNotNull('reward_id')
                            ->distinct('character_id')
                            ->count('character_id');

                        return "{$rewardedCount}/{$participantCount} คน";
                    }),

                Tables\Columns\TextColumn::make('kingdom.name')
                    ->label('อาณาจักร')
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
                Tables\Actions\Action::make('closeEvent')
                    ->label('ปิด Event')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn (Event $record): bool => $record->status === 'active')
                    ->requiresConfirmation()
                    ->modalHeading('ปิด Event นี้?')
                    ->modalDescription(function (Event $record): HtmlString {
                        $participantCount = $record->participants()->count();
                        $openThreadCount  = $record->threads()->whereNotIn('status', ['locked', 'archived'])->count();
                        $rewardedCount    = RewardLog::where('event_id', $record->id)
                            ->whereNotNull('reward_id')
                            ->distinct('character_id')
                            ->count('character_id');

                        return new HtmlString(
                            "ผู้เข้าร่วม {$participantCount} คน · ได้รับ reward ไปแล้ว {$rewardedCount} คน"
                            . ' (ระบบแจกอัตโนมัติทันทีที่โพสต์ได้รับอนุมัติ — ปิด Event จะไม่แจกเพิ่ม)<br><br>'
                            . "การปิดจะล็อคกระทู้ที่ผูกกับ Event นี้ทั้งหมด <strong>{$openThreadCount} กระทู้</strong> ทันที ผู้เล่นจะโพสต์เพิ่มไม่ได้"
                        );
                    })
                    ->modalSubmitActionLabel('ยืนยันปิด Event')
                    ->action(function (Event $record) {
                        DB::transaction(function () use ($record) {
                            $record->update(['status' => 'closed']);

                            $threads = $record->threads()->whereNotIn('status', ['locked', 'archived'])->get();
                            foreach ($threads as $thread) {
                                $thread->update(['status' => 'locked']);
                                app(NotificationService::class)->notifyThreadLocked($thread);
                            }
                        });

                        FilamentNotification::make()
                            ->title('ปิด Event และล็อคกระทู้ที่เกี่ยวข้องแล้ว')
                            ->success()
                            ->send();
                    }),
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

    /**
     * A Thread belongs to a City, but an Event only optionally belongs to a
     * Kingdom — so "create a thread for this Event" has to guess a City to
     * land the admin on. Picks the Event's Kingdom's first City by name, or
     * (for a Kingdom-less Event) the first City system-wide. Null means no
     * City exists to route to at all.
     */
    public static function resolveThreadTargetCity(Event $event): ?City
    {
        return $event->kingdom_id
            ? City::where('kingdom_id', $event->kingdom_id)->orderBy('name')->first()
            : City::orderBy('name')->first();
    }
}
