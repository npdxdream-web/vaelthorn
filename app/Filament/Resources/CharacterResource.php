<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CharacterResource\Pages;
use App\Filament\Resources\CharacterResource\RelationManagers\BadgesRelationManager;
use App\Filament\Resources\CharacterResource\RelationManagers\InventoryRelationManager;
use App\Models\Character;
use App\Models\OnboardingEntry;
use App\Services\NotificationService;
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

    /**
     * Marks onboarding as approved — does NOT make the character playable yet.
     * 'approved' forces the character to /choose-kingdom on every request
     * (EnsureKingdomSelected); KingdomSelectionController::store() is what
     * flips status to 'active', automatically, the moment they pick a kingdom.
     */
    public static function approveCharacter(Character $record): void
    {
        $record->update(['status' => 'approved']);
    }

    /**
     * Reject one or more specific onboarding stages — only the selected stages'
     * entries/flags/reasons are touched, the rest are left completely alone.
     * Status stays 'pending' (not 'rejected') so this remains a normal
     * "send back for revision" loop rather than a terminal state.
     *
     * @param array<int,string> $stageReasons stage number => reason, e.g. [2 => '...']
     */
    public static function rejectCharacter(Character $record, array $stageReasons): void
    {
        OnboardingEntry::where('character_id', $record->id)
            ->whereIn('stage', array_keys($stageReasons))
            ->delete();

        $stats = $record->stats;
        if ($stats) {
            $updates = [];
            foreach ($stageReasons as $stage => $reason) {
                $updates["stage_{$stage}_completed"]         = false;
                $updates["stage_{$stage}_rejection_reason"]  = $reason;
            }
            $stats->update($updates);
        }

        app(NotificationService::class)->notifyOnboardingRejected($record, array_keys($stageReasons));
    }

    /**
     * Reject form: one Toggle+Textarea pair per stage that's actually been
     * submitted (stage_X_completed) — nothing to reject on a stage the player
     * hasn't gotten to yet. Shared by CharacterResource's + UserResource's
     * Reject actions and EditCharacter's header action.
     */
    public static function rejectFormSchema(?Character $record): array
    {
        $stageLabels = [1 => 'ตัวตน', 2 => 'เหตุ', 3 => 'ปณิธาน'];
        $stats       = $record?->stats;
        $schema      = [];

        foreach ($stageLabels as $num => $label) {
            if (! ($stats?->{"stage_{$num}_completed"} ?? false)) {
                continue;
            }

            $schema[] = Forms\Components\Checkbox::make("reject_stage_{$num}")
                ->label("บทที่ {$num} — {$label}")
                ->live();

            $schema[] = Forms\Components\Textarea::make("reason_stage_{$num}")
                ->label('เหตุผล')
                ->rows(3)
                ->visible(fn (Forms\Get $get) => (bool) $get("reject_stage_{$num}"))
                ->required(fn (Forms\Get $get) => (bool) $get("reject_stage_{$num}"));
        }

        return $schema;
    }

    /**
     * Builds the stage=>reason map from the reject form's submitted data and
     * calls rejectCharacter(). Returns false if nothing was actually selected
     * (caller should show a validation-style notification in that case).
     */
    public static function handleRejectSubmit(Character $record, array $data): bool
    {
        $stageReasons = [];

        foreach ([1, 2, 3] as $num) {
            if (! empty($data["reject_stage_{$num}"]) && filled($data["reason_stage_{$num}"] ?? null)) {
                $stageReasons[$num] = $data["reason_stage_{$num}"];
            }
        }

        if (empty($stageReasons)) {
            return false;
        }

        static::rejectCharacter($record, $stageReasons);

        return true;
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
                    Forms\Components\Select::make('kingdom_id')
                        ->relationship('kingdom', 'name')
                        ->label('อาณาจักร'),
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

            Forms\Components\Section::make('หลักฐาน 3 ด่าน (Onboarding)')
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
                            $done          = $stats?->$flag ? '(flag set)' : '';
                            $reasonField   = "stage_{$num}_rejection_reason";
                            $rejectReason  = $stats?->$reasonField;
                            $content       = '<span style="color:#6b6050">ยังไม่ได้ส่ง ' . $done . '</span>';

                            if ($rejectReason) {
                                $content .= '<div style="margin-top:0.4rem;color:#f87171;font-size:0.8rem">'
                                    . 'ถูก Reject: ' . e($rejectReason) . '</div>';
                            }

                            $components[] = Forms\Components\Placeholder::make("entry_stage_{$num}")
                                ->label("○ {$label}")
                                ->content(new \Illuminate\Support\HtmlString($content));
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
                Tables\Columns\TextColumn::make('kingdom.name')
                    ->label('อาณาจักร')
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
                            : 'ตัวละครผ่าน Onboarding แล้ว (Level ' . ($record->stats?->level ?? '?') . ') — จะถูก set เป็น Approved และบังคับให้เลือกอาณาจักรก่อน ถึงจะกลายเป็น Active'
                    )
                    ->action(function (Character $record) {
                        static::approveCharacter($record);
                        Notification::make()
                            ->title("Approve '{$record->name}' สำเร็จ — รอผู้เล่นเลือกอาณาจักร")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Character $record) => $record->status === 'pending'),

                // ── Reject ────────────────────────────────────────────────────
                Tables\Actions\Action::make('reject_character')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form(fn (Character $record) => static::rejectFormSchema($record))
                    ->action(function (Character $record, array $data) {
                        if (! static::handleRejectSubmit($record, $data)) {
                            Notification::make()
                                ->title('กรุณาเลือกอย่างน้อย 1 บท พร้อมเหตุผล')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title("Reject '{$record->name}' แล้ว — แจ้งเหตุผลและรีเซ็ตบทที่เลือกให้ทำใหม่แล้ว")
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
            BadgesRelationManager::class,
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
