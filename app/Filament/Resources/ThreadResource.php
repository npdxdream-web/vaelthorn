<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThreadResource\Pages;
use App\Models\Thread;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ThreadResource extends Resource
{
    protected static ?string $model = Thread::class;

    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'กระทู้';
    protected static ?string $navigationGroup = 'ฟอรั่ม';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('village_id')
                ->label('หมู่บ้าน')
                ->options(fn () => Village::with('city')->get()->mapWithKeys(fn ($v) => [$v->id => "{$v->city?->name} → {$v->name}"]))
                ->searchable()
                ->required(),
            Forms\Components\Select::make('created_by')
                ->label('ผู้เขียน')
                ->relationship('creator', 'name')
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('title')
                ->label('หัวข้อ')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('status')
                ->label('สถานะ')
                ->options([
                    'draft'        => 'ฉบับร่าง',
                    'pending'      => 'รออนุมัติ',
                    'approved'     => 'อนุมัติแล้ว (Live)',
                    'request_edit' => 'ขอแก้ไข',
                    'rejected'     => 'ปฏิเสธ',
                    'locked'       => 'ล็อค',
                    'archived'     => 'เก็บถาวร',
                ])
                ->required()
                ->default('pending'),
            Forms\Components\Textarea::make('moderation_message')
                ->label('ข้อความจาก Admin')
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('หัวข้อ')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('ผู้เขียน')
                    ->searchable(),
                Tables\Columns\TextColumn::make('village.name')
                    ->label('หมู่บ้าน')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('สถานะ')
                    ->colors([
                        'success'   => 'approved',
                        'warning'   => 'pending',
                        'secondary' => ['draft', 'locked', 'archived'],
                        'danger'    => ['rejected', 'request_edit'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved'     => 'Live',
                        'pending'      => 'รออนุมัติ',
                        'draft'        => 'ร่าง',
                        'request_edit' => 'ขอแก้ไข',
                        'rejected'     => 'ปฏิเสธ',
                        'locked'       => 'ล็อค',
                        'archived'     => 'เก็บถาวร',
                        default        => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('เวลา')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'draft'        => 'ฉบับร่าง',
                        'pending'      => 'รออนุมัติ',
                        'approved'     => 'Live',
                        'request_edit' => 'ขอแก้ไข',
                        'rejected'     => 'ปฏิเสธ',
                        'locked'       => 'ล็อค',
                        'archived'     => 'เก็บถาวร',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('อนุมัติ')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Thread $record) => $record->update(['status' => 'approved', 'moderation_message' => null]))
                    ->visible(fn (Thread $record) => ! $record->trashed() && in_array($record->status, ['pending', 'request_edit', 'draft'], true)),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('requestEdit')
                        ->label('ขอแก้ไข')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->form([
                            Forms\Components\Textarea::make('message')
                                ->label('ข้อความถึงผู้เขียน')
                                ->required()
                                ->rows(4),
                        ])
                        ->action(fn (Thread $record, array $data) => $record->update(['status' => 'request_edit', 'moderation_message' => $data['message']]))
                        ->visible(fn (Thread $record) => ! $record->trashed()),

                    Tables\Actions\Action::make('reject')
                        ->label('ปฏิเสธ')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('message')
                                ->label('เหตุผล')
                                ->required()
                                ->rows(4),
                        ])
                        ->action(fn (Thread $record, array $data) => $record->update(['status' => 'rejected', 'moderation_message' => $data['message']]))
                        ->visible(fn (Thread $record) => ! $record->trashed()),

                    Tables\Actions\Action::make('lock')
                        ->label('ล็อคกระทู้')
                        ->icon('heroicon-o-lock-closed')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn (Thread $record) => $record->update(['status' => 'locked']))
                        ->visible(fn (Thread $record) => ! $record->trashed() && $record->status !== 'locked'),

                    Tables\Actions\Action::make('unlock')
                        ->label('ปลดล็อค')
                        ->icon('heroicon-o-lock-open')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Thread $record) => $record->update(['status' => 'approved']))
                        ->visible(fn (Thread $record) => ! $record->trashed() && $record->status === 'locked'),

                    Tables\Actions\Action::make('archive')
                        ->label('Archive Story Arc')
                        ->icon('heroicon-o-archive-box')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Archive Story Arc')
                        ->modalDescription('Thread จะถูกเปลี่ยนเป็น archived — ยังคงอ่านได้จากหน้า Chronicle Archive แต่จะไม่แสดงใน village feed อีกต่อไป ไม่มีการลบข้อมูลจริง')
                        ->action(fn (Thread $record) => $record->update([
                            'status'      => 'archived',
                            'archived_at' => now(),
                        ]))
                        ->visible(fn (Thread $record) => ! $record->trashed() && $record->status !== 'archived'),

                    Tables\Actions\Action::make('unarchive')
                        ->label('ยกเลิกเก็บถาวร')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(fn (Thread $record) => $record->update(['status' => 'approved']))
                        ->visible(fn (Thread $record) => ! $record->trashed() && $record->status === 'archived'),

                    Tables\Actions\Action::make('move')
                        ->label('ย้ายหมู่บ้าน')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('village_id')
                                ->label('หมู่บ้านปลายทาง')
                                ->options(fn () => Village::with('city')->get()->mapWithKeys(fn ($v) => [$v->id => "{$v->city?->name} → {$v->name}"]))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(fn (Thread $record, array $data) => $record->update(['village_id' => $data['village_id']]))
                        ->visible(fn (Thread $record) => ! $record->trashed()),

                    Tables\Actions\EditAction::make()
                        ->label('แก้ไขข้อมูล')
                        ->visible(fn (Thread $record) => ! $record->trashed()),

                    Tables\Actions\DeleteAction::make()
                        ->label('ย้ายไปถังขยะ')
                        ->visible(fn (Thread $record) => ! $record->trashed()),

                    Tables\Actions\RestoreAction::make()
                        ->label('กู้คืน')
                        ->visible(fn (Thread $record) => $record->trashed()),

                    Tables\Actions\ForceDeleteAction::make()
                        ->label('ลบถาวร')
                        ->visible(fn (Thread $record) => $record->trashed()),
                ])
                ->icon('heroicon-o-ellipsis-vertical')
                ->tooltip('การดำเนินการ'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('ย้ายไปถังขยะ'),
                    Tables\Actions\RestoreBulkAction::make()->label('กู้คืน'),
                    Tables\Actions\ForceDeleteBulkAction::make()->label('ลบถาวร'),
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
            'index'  => Pages\ListThreads::route('/'),
            'create' => Pages\CreateThread::route('/create'),
            'edit'   => Pages\EditThread::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            \Illuminate\Database\Eloquent\SoftDeletingScope::class,
        ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAtLeastModerator() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isAtLeastModerator() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }

    public static function canRestore($record): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }

    public static function canForceDelete($record): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }
}
