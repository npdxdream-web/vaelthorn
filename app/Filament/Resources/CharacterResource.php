<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CharacterResource\Pages;
use App\Filament\Resources\CharacterResource\RelationManagers\InventoryRelationManager;
use App\Models\Character;
use App\Models\OnboardingEntry;
use App\Models\OnboardingSlot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CharacterResource extends Resource
{
    protected static ?string $model = Character::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'ตัวละคร';
    protected static ?string $navigationGroup = 'สมาชิก';
    protected static ?int $navigationSort = 2;
    protected static bool $shouldRegisterNavigation = false;

    // ─── Shared approve logic ─────────────────────────────────────────────────

    public static function approveCharacter(Character $record): void
    {
        $record->update(['status' => 'active']);
        // Level promotion (0→1) is handled by OnboardingService when stages complete.
        // If admin approves before stages are done, character stays at level 0 and
        // continues onboarding on /onboarding, then chooses city after completion.
    }

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ข้อมูลตัวละคร')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('User'),
                    Forms\Components\Select::make('city_id')
                        ->relationship('city', 'name')
                        ->label('เมือง'),
                    Forms\Components\TextInput::make('name')
                        ->label('ชื่อตัวละคร'),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending'  => 'Pending',
                            'active'   => 'Active',
                            'rejected' => 'Rejected',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->label('Title พิเศษ')
                        ->placeholder('เช่น Guardian of the North')
                        ->maxLength(100)
                        ->nullable()
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('gold')
                        ->label('Gold')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                    Forms\Components\Textarea::make('backstory')
                        ->label('Backstory')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('RPG Stats')
                ->relationship('stats')
                ->columns(5)
                ->schema([
                    Forms\Components\TextInput::make('level')->label('Level')->numeric()->default(0)->minValue(0),
                    Forms\Components\TextInput::make('hp')->label('HP')->numeric()->default(100)->minValue(0),
                    Forms\Components\TextInput::make('mana')->label('Mana')->numeric()->default(50)->minValue(0),
                    Forms\Components\TextInput::make('str')->label('STR')->numeric()->default(10)->minValue(0),
                    Forms\Components\TextInput::make('agi')->label('AGI')->numeric()->default(10)->minValue(0),
                ]),

            Forms\Components\Section::make('Onboarding Progress (Level 0 → 1)')
                ->description('แสดงเฉพาะเมื่อ Level = 0 — ติดตาม Stage A/B ก่อน Approve')
                ->collapsible()
                ->collapsed()
                ->visible(fn ($record) => $record?->stats?->level === 0)
                ->schema([
                    Forms\Components\Placeholder::make('stage_a_status')
                        ->label('Stage A — บันทึกตัวตน (Training Zone)')
                        ->content(function ($record) {
                            if (! $record) {
                                return 'ไม่มีข้อมูล';
                            }
                            $stats = $record->stats;
                            $slots = OnboardingSlot::where('character_id', $record->id)
                                ->orderBy('slot_number')->get();
                            $filled = $slots->where('status', 'filled')->count();

                            if ($stats?->stage_a_completed) {
                                return new \Illuminate\Support\HtmlString(
                                    '<span style="color:#4ade80">✓ สำเร็จ (3/3 slot filled)</span>'
                                );
                            }

                            $parts = $slots->map(fn ($s) =>
                                $s->status === 'filled'
                                    ? "<span style='color:#c8a84b'>Slot {$s->slot_number} ✓ (Post #{$s->post_id})</span>"
                                    : "<span style='color:#3a3020'>Slot {$s->slot_number} ○ (empty)</span>"
                            )->join(' &nbsp;');

                            return new \Illuminate\Support\HtmlString(
                                "{$parts}<br><small style='color:#6b6050'>{$filled}/3 filled</small>"
                            );
                        }),

                    Forms\Components\Placeholder::make('stage_b_status')
                        ->label('Stage B — EXP จาก Event Onboarding')
                        ->content(function ($record) {
                            if (! $record) {
                                return 'ไม่มีข้อมูล';
                            }
                            $stats     = $record->stats;
                            $stageBExp = $stats?->stage_b_exp ?? 0;
                            $required  = config('leveling.stage_b_required_exp', 6);

                            if (! $stats?->stage_a_completed) {
                                return new \Illuminate\Support\HtmlString(
                                    '<span style="color:#6b6050">รอ Stage A สำเร็จก่อน</span>'
                                );
                            }

                            $color = $stageBExp >= $required ? '#4ade80' : '#c8a84b';
                            return new \Illuminate\Support\HtmlString(
                                "<span style='color:{$color}'>{$stageBExp}/{$required} EXP</span>"
                            );
                        }),

                    Forms\Components\Placeholder::make('stage_a_warning')
                        ->label('⚠ ความผิดปกติ')
                        ->content(function ($record) {
                            if (! $record) {
                                return null;
                            }
                            $stats  = $record->stats;
                            $filled = OnboardingSlot::where('character_id', $record->id)
                                ->where('status', 'filled')->count();

                            if ($stats?->stage_a_completed && $filled < 3) {
                                return new \Illuminate\Support\HtmlString(
                                    '<span style="color:#f87171">stage_a_completed = true แต่ filled slot เหลือ '
                                    . $filled . '/3 — อาจเกิดจาก Reward Audit revoke. Admin ต้องตัดสินใจเอง</span>'
                                );
                            }
                            return new \Illuminate\Support\HtmlString('<span style="color:#6b6050">ปกติ</span>');
                        })
                        ->visible(fn ($record) => $record?->stats?->stage_a_completed ?? false),
                ]),

            Forms\Components\Section::make('หลักฐาน 3 ด่าน (Onboarding ใหม่)')
                ->description('บันทึกที่ผู้เล่นส่งในแต่ละด่าน — อ่านก่อนกด Approve')
                ->collapsible()
                ->schema(function ($record) {
                    if (! $record) {
                        return [Forms\Components\Placeholder::make('no_entries')->label('')->content('ยังไม่มีข้อมูล')];
                    }

                    $stageLabels = [
                        1 => 'ด่าน 1 — บันทึกตัวตน',
                        2 => 'ด่าน 2 — แรงผลักดัน',
                        3 => 'ด่าน 3 — ก้าวแรกสู่โลก',
                    ];

                    $entries = OnboardingEntry::where('character_id', $record->id)
                        ->orderBy('stage')->get()->keyBy('stage');

                    $components = [];
                    $stats      = $record->stats;

                    foreach ($stageLabels as $num => $label) {
                        $entry = $entries->get($num);
                        $flag  = "stage_{$num}_completed";

                        if ($entry) {
                            $submittedAt = $entry->submitted_at?->format('d M Y H:i') ?? '';
                            $components[] = Forms\Components\Placeholder::make("entry_stage_{$num}")
                                ->label("✓ {$label} — {$submittedAt}")
                                ->content(new \Illuminate\Support\HtmlString(
                                    '<div style="white-space:pre-wrap;color:#c8c6c3;font-size:0.85rem;line-height:1.6">'
                                    . e($entry->content) . '</div>'
                                ));
                        } else {
                            $done  = $stats?->$flag ? '(flag set)' : '';
                            $components[] = Forms\Components\Placeholder::make("entry_stage_{$num}")
                                ->label("○ {$label}")
                                ->content(new \Illuminate\Support\HtmlString(
                                    '<span style="color:#6b6050">ยังไม่ได้ส่ง ' . $done . '</span>'
                                ));
                        }
                    }

                    return $components;
                }),
        ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('posts'))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('ตัวละคร')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->label('เมือง')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stats.level')
                    ->label('Level')
                    ->badge()
                    ->color(fn ($state) => match ((int) $state) {
                        0       => 'gray',
                        1       => 'warning',
                        default => 'success',
                    })
                    ->default('—'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger'  => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('auto_rank')
                    ->label('Rank')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Legend'   => 'danger',
                        'Veteran'  => 'warning',
                        'Traveler' => 'info',
                        'Wanderer' => 'success',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->default('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สมัครเมื่อ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'active'   => 'Active',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                // ── Approve Character ─────────────────────────────────────────
                Tables\Actions\Action::make('approve_character')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve ตัวละคร')
                    ->modalDescription(fn (Character $record) =>
                        $record->stats?->level === 0
                            ? 'ตัวละครยังอยู่ที่ Level 0 — จะถูก Approve และเลื่อนเป็น Level 1 ทันที'
                            : 'ตัวละครผ่าน Onboarding แล้ว (Level ' . ($record->stats?->level ?? '?') . ') — จะถูก set status เป็น Active'
                    )
                    ->action(function (Character $record) {
                        static::approveCharacter($record);
                        Notification::make()
                            ->title("Approve '{$record->name}' สำเร็จ")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Character $record) => $record->status === 'pending'),

                // ── Reject ────────────────────────────────────────────────────
                Tables\Actions\Action::make('reject_character')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject ตัวละคร')
                    ->modalDescription('ยืนยันการ Reject — ตัวละครจะถูก set เป็น Rejected')
                    ->action(function (Character $record) {
                        $record->update(['status' => 'rejected']);
                        Notification::make()
                            ->title("Reject '{$record->name}' แล้ว")
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Character $record) => $record->status === 'pending'),

                Tables\Actions\EditAction::make()->label('จัดการ'),
            ])
            ->bulkActions([]);
    }

    // ─── Meta ─────────────────────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [
            InventoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCharacters::route('/'),
            'edit'  => Pages\EditCharacter::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
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
