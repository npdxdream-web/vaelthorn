<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardAuditResource\Pages;
use App\Models\RewardLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RewardAuditResource extends Resource
{
    protected static ?string $model = RewardLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Reward Audit Log';
    protected static ?string $navigationGroup = 'รายงาน';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('character.name')
                    ->label('ตัวละคร')
                    ->searchable(),

                Tables\Columns\TextColumn::make('exp_received')
                    ->label('EXP')
                    ->badge()
                    ->color(fn (RewardLog $record) => $record->revoked ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state, RewardLog $record) => $record->revoked ? "-{$state} (Revoked)" : "+{$state}"),

                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->default('—')
                    ->limit(30),

                Tables\Columns\TextColumn::make('post_id')
                    ->label('Post')
                    ->formatStateUsing(fn ($state) => $state ? "#{$state}" : '—')
                    ->url(fn (RewardLog $record) => $record->post_id
                        ? route('thread', optional($record->post)->thread_id)
                        : null)
                    ->openUrlInNewTab(),

                Tables\Columns\IconColumn::make('revoked')
                    ->label('Revoked')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('revoked_at')
                    ->label('Revoked At')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('revokedBy.name')
                    ->label('Revoked By')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('given_at')
                    ->label('Given At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('revoked')
                    ->label('สถานะ')
                    ->options([
                        '0' => 'Active',
                        '1' => 'Revoked',
                    ]),
                Tables\Filters\SelectFilter::make('character_id')
                    ->label('ตัวละคร')
                    ->relationship('character', 'name'),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRewardAudit::route('/'),
        ];
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }
}
