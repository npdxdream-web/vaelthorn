<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\Kingdom;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'จัดการสมาชิก';
    protected static ?string $navigationGroup = 'สมาชิก';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;

        return $form->schema([
            Forms\Components\Section::make('ข้อมูลสมาชิก')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('ชื่อผู้ใช้')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label('อีเมล')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('role')
                        ->label('Role')
                        ->options(collect(UserRole::cases())->mapWithKeys(
                            fn (UserRole $r) => [$r->value => $r->label()]
                        ))
                        ->required()
                        ->disabled(! $isSuperAdmin)
                        ->dehydrated($isSuperAdmin),

                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->placeholder('เว้นว่างถ้าไม่ต้องการเปลี่ยน')
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->rules([
                            fn (): Closure =>
                                function (string $attribute, $value, Closure $fail) {
                                    if (filled($value) && mb_strlen($value) < 6) {
                                        $fail('Password ต้องมีอย่างน้อย 6 ตัวอักษร');
                                    }
                                },
                        ])
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('ตัวละคร')
                ->relationship('character')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('ชื่อตัวละคร')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('kingdom_id')
                        ->label('อาณาจักร')
                        ->options(fn () => Kingdom::orderBy('name')->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending'   => 'Pending',
                            'approved'  => 'Approved',
                            'active'    => 'Active',
                            'rejected'  => 'Rejected',
                            'suspended' => 'Suspended',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->label('Title พิเศษ')
                        ->placeholder('เช่น Guardian of the North')
                        ->maxLength(100)
                        ->nullable(),
                    Forms\Components\TextInput::make('gold')
                        ->label('Gold')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    Forms\Components\ToggleButtons::make('custom_frame')
                        ->label('กรอบ Avatar')
                        ->helperText('ปล่อยว่างให้ระบบเลือกกรอบตาม Rank อัตโนมัติ')
                        ->options([
                            'legend'   => 'Legend',
                            'veteran'  => 'Veteran',
                            'traveler' => 'Traveler',
                            'wanderer' => 'Wanderer',
                            'stranger' => 'Stranger',
                            'admin'    => 'Admin',
                            'moderator'=> 'Moderator',
                        ])
                        ->colors([
                            'legend'   => 'warning',
                            'veteran'  => 'danger',
                            'traveler' => 'info',
                            'wanderer' => 'success',
                            'stranger' => 'gray',
                            'admin'    => 'warning',
                            'moderator'=> 'primary',
                        ])
                        ->icons([
                            'legend'   => 'heroicon-o-star',
                            'veteran'  => 'heroicon-o-shield-check',
                            'traveler' => 'heroicon-o-map',
                            'wanderer' => 'heroicon-o-globe-alt',
                            'stranger' => 'heroicon-o-user',
                            'admin'    => 'heroicon-o-sparkles',
                            'moderator'=> 'heroicon-o-shield-exclamation',
                        ])
                        ->nullable()
                        ->inline()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('การตอบพิธีเข้าสู่โลก (Onboarding)')
                ->description('คำตอบทั้ง 3 บทที่ผู้เล่นกรอกไว้ — ใช้ประกอบการพิจารณาก่อนอนุมัติ')
                ->collapsible()
                ->schema([
                    Forms\Components\Placeholder::make('onboarding_stage_1')
                        ->label('บทที่ ๑ · ตัวตน')
                        ->content(fn ($record) => self::onboardingStageContent($record, 1)),
                    Forms\Components\Placeholder::make('onboarding_stage_2')
                        ->label('บทที่ ๒ · เหตุ')
                        ->content(fn ($record) => self::onboardingStageContent($record, 2)),
                    Forms\Components\Placeholder::make('onboarding_stage_3')
                        ->label('บทที่ ๓ · ปณิธาน')
                        ->content(fn ($record) => self::onboardingStageContent($record, 3)),
                ]),

            Forms\Components\Section::make('RPG Stats')
                ->relationship('character')
                ->schema([
                    Forms\Components\Fieldset::make('stats')
                        ->relationship('stats')
                        ->columns(5)
                        ->schema([
                            Forms\Components\TextInput::make('level')->label('Level')->numeric()->default(1)->minValue(1),
                            Forms\Components\TextInput::make('hp')->label('HP')->numeric()->default(100)->minValue(0),
                            Forms\Components\TextInput::make('mana')->label('Mana')->numeric()->default(50)->minValue(0),
                            Forms\Components\TextInput::make('str')->label('STR')->numeric()->default(10)->minValue(0),
                            Forms\Components\TextInput::make('agi')->label('AGI')->numeric()->default(10)->minValue(0),
                        ]),
                ]),
        ]);
    }

    private static function onboardingStageContent($record, int $stage): HtmlString
    {
        $entry = $record?->character?->onboardingEntries?->firstWhere('stage', $stage);

        if (! $entry) {
            return new HtmlString('<span class="text-gray-500">ยังไม่ได้ตอบ</span>');
        }

        $submittedAt = $entry->submitted_at?->format('d M Y H:i');

        return new HtmlString(
            '<div class="whitespace-pre-line text-sm">'.e($entry->content).'</div>'
            .($submittedAt ? '<div class="mt-1 text-xs text-gray-500">ส่งเมื่อ '.e($submittedAt).'</div>' : '')
        );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อผู้ใช้')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('อีเมล')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(fn (UserRole $state) => $state->label())
                    ->colors([
                        'danger'  => UserRole::SuperAdmin->value,
                        'warning' => UserRole::Admin->value,
                        'info'    => UserRole::Moderator->value,
                        'gray'    => UserRole::Player->value,
                    ]),
                Tables\Columns\TextColumn::make('character.name')
                    ->label('ตัวละคร')
                    ->searchable()
                    ->default('—'),
                Tables\Columns\BadgeColumn::make('character.status')
                    ->label('สถานะตัวละคร')
                    ->colors([
                        'warning' => 'pending',
                        'success' => ['active', 'approved'],
                        'danger'  => 'rejected',
                        'gray'    => 'suspended',
                    ])
                    ->default('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สมัครเมื่อ')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options(collect(UserRole::cases())->mapWithKeys(
                        fn (UserRole $r) => [$r->value => $r->label()]
                    )),
                Tables\Filters\SelectFilter::make('character_status')
                    ->label('สถานะตัวละคร')
                    ->options([
                        'pending'   => 'Pending',
                        'approved'  => 'Approved',
                        'active'    => 'Active',
                        'rejected'  => 'Rejected',
                        'suspended' => 'Suspended',
                    ])
                    ->query(fn ($query, array $data) => $data['value']
                        ? $query->whereHas('character', fn ($q) => $q->where('status', $data['value']))
                        : $query),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('จัดการ'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit'  => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
