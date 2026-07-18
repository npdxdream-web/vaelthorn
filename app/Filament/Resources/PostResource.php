<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Models\RewardLog;
use App\Services\LevelingService;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'โพสต์';
    protected static ?string $navigationGroup = 'ฟอรั่ม';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('thread_id')
                ->relationship('thread', 'title')
                ->label('กระทู้')
                ->disabled(),
            Forms\Components\Select::make('character_id')
                ->relationship('character', 'name')
                ->label('ตัวละคร')
                ->disabled(),
            Forms\Components\Textarea::make('content')
                ->label('เนื้อหา')
                ->disabled()
                ->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'pending'  => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->disabled()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('thread.title')
                    ->label('กระทู้')
                    ->searchable(),
                Tables\Columns\TextColumn::make('character.name')
                    ->label('ตัวละคร')
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('เนื้อหา')
                    ->limit(50),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                    ]),
                // Show EXP badge when this post has reward_logs
                Tables\Columns\TextColumn::make('reward_logs_sum_exp_received')
                    ->label('EXP')
                    ->getStateUsing(fn (Post $record) => RewardLog::where('post_id', $record->id)->where('revoked', false)->sum('exp_received'))
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "+{$state}" : null)
                    ->default(null),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('เวลา')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                // Approve
                Tables\Actions\Action::make('approve')
                    ->label('อนุมัติ')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Post $record) {
                        $record->load(['character.user', 'thread.event']);
                        $record->update(['status' => 'approved']);
                        app(NotificationService::class)->notifyPostApproved($record);
                        app(LevelingService::class)->handlePostApproved($record);
                    })
                    ->visible(fn (Post $record) => $record->status === 'pending'),

                Tables\Actions\EditAction::make()->label('จัดการ'),

                // Delete with EXP-revoke option
                Tables\Actions\Action::make('delete_post')
                    ->label('ลบ')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Post $record) => static::buildDeleteModalHeading($record))
                    ->modalDescription(fn (Post $record) => static::buildDeleteModalDescription($record))
                    ->modalSubmitActionLabel('ลบโพสต์เท่านั้น')
                    ->extraModalFooterActions(fn (Post $record): array => static::buildRevokeFooterAction($record))
                    ->action(fn (Post $record) => $record->delete())
                    ->visible(fn () => auth()->user()?->isAtLeastAdmin() ?? false),
            ])
            ->bulkActions([]);
    }

    // ─── Delete modal helpers ─────────────────────────────────────────────────

    private static function buildDeleteModalHeading(Post $record): string
    {
        $total = RewardLog::where('post_id', $record->id)->where('revoked', false)->sum('exp_received');
        return $total > 0
            ? "ลบโพสต์ที่มี EXP +{$total}"
            : 'ยืนยันการลบโพสต์';
    }

    private static function buildDeleteModalDescription(Post $record): string
    {
        $logs = RewardLog::where('post_id', $record->id)->where('revoked', false)->get();
        if ($logs->isEmpty()) {
            return 'โพสต์นี้ไม่มี EXP ผูกอยู่ — ลบได้เลย';
        }
        $total = $logs->sum('exp_received');
        $logIds = $logs->pluck('id')->join(', #');
        return "โพสต์นี้เคยได้รับ EXP +{$total} (Reward Log #{$logIds})\n"
            . "ต้องการลบโพสต์เฉย ๆ หรือลบพร้อม Revoke EXP ด้วย?";
    }

    private static function buildRevokeFooterAction(Post $record): array
    {
        $total = RewardLog::where('post_id', $record->id)->where('revoked', false)->sum('exp_received');
        if ($total <= 0) {
            return [];
        }

        return [
            Tables\Actions\Action::make('delete_and_revoke')
                ->label("ลบ + Revoke EXP +{$total}")
                ->color('danger')
                ->action(function () use ($record, $total) {
                    // Revoke all active reward logs for this post
                    RewardLog::where('post_id', $record->id)
                        ->where('revoked', false)
                        ->each(function (RewardLog $log) {
                            $log->update([
                                'revoked'    => true,
                                'revoked_at' => now(),
                                'revoked_by' => Auth::id(),
                            ]);
                            // Deduct exp from character
                            $log->character?->stats?->decrement('exp', $log->exp_received);
                        });

                    $record->delete();

                    Notification::make()
                        ->title("ลบโพสต์และ Revoke EXP +{$total} แล้ว")
                        ->success()
                        ->send();
                }),
        ];
    }

    // ─── Meta ─────────────────────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'edit'  => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool { return false; }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAtLeastModerator() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isAtLeastModerator() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }
}
