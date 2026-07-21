<?php

namespace App\Filament\Pages;

use App\Models\Thread;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ThreadTrash extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-trash';
    protected static ?string $navigationLabel = 'ถังขยะกระทู้';
    protected static ?string $navigationGroup = 'ฟอรั่ม';
    protected static ?int    $navigationSort  = 9;
    protected static string  $view            = 'filament.pages.thread-trash';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAtLeastAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Thread::onlyTrashed()->with(['creator', 'city.kingdom']))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('หัวข้อ')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('ผู้เขียน'),
                Tables\Columns\TextColumn::make('city.name')
                    ->label('เมือง'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('ลบเมื่อ')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('วันที่เหลือ')
                    ->getStateUsing(fn (Thread $record): string => max(0, 3 - (int) $record->deleted_at->diffInDays(now())) . ' วัน'),
            ])
            ->defaultSort('deleted_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('restore')
                    ->label('กู้คืน')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Thread $record) => $record->restore()),

                Tables\Actions\Action::make('forceDelete')
                    ->label('ลบถาวร')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Thread $record) => $record->forceDelete()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('restoreAll')
                        ->label('กู้คืนที่เลือก')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->restore()),

                    Tables\Actions\BulkAction::make('forceDeleteAll')
                        ->label('ลบถาวรที่เลือก')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->forceDelete()),
                ]),
            ]);
    }
}
