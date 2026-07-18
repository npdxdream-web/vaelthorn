<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\City;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Closure;
use Illuminate\Support\Facades\Hash;

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

                    Forms\Components\Group::make([
                        Forms\Components\TextInput::make('password')
                            ->label('Password ใหม่')
                            ->password()
                            ->revealable()
                            ->placeholder('เว้นว่างถ้าไม่ต้องการเปลี่ยน')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->rules([
                                fn (Forms\Get $get): Closure =>
                                    function (string $attribute, $value, Closure $fail) use ($get) {
                                        if (! filled($value)) return;
                                        if (mb_strlen($value) < 6) {
                                            $fail('Password ต้องมีอย่างน้อย 6 ตัวอักษร');
                                            return;
                                        }
                                        if ($value !== $get('password_confirmation')) {
                                            $fail('Password ยืนยันไม่ตรงกัน');
                                        }
                                    },
                            ]),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('ยืนยัน Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(false),
                    ])->columnSpan(1),
                ]),

            Forms\Components\Section::make('ตัวละคร')
                ->relationship('character')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('ชื่อตัวละคร')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('city_id')
                        ->label('เมือง')
                        ->options(fn () => City::orderBy('name')->pluck('name', 'id'))
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

                    Forms\Components\Textarea::make('backstory')
                        ->label('Backstory')
                        ->columnSpanFull(),
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
