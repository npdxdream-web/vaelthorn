<?php

namespace App\Filament\Resources\CharacterResource\Pages;

use App\Filament\Resources\CharacterResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCharacter extends EditRecord
{
    protected static string $resource = CharacterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Approve Character ─────────────────────────────────────────────
            Actions\Action::make('approve_character')
                ->label('Approve ตัวละคร')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve ตัวละคร')
                ->modalDescription(function () {
                    $record = $this->getRecord();
                    return $record->stats?->level === 0
                        ? 'ตัวละครยังอยู่ที่ Level 0 — จะถูก Approve และเลื่อนเป็น Level 1 ทันที'
                        : 'ตัวละครผ่าน Onboarding แล้ว (Level ' . ($record->stats?->level ?? '?') . ') — จะถูก set status เป็น Active';
                })
                ->action(function () {
                    $record = $this->getRecord();
                    CharacterResource::approveCharacter($record);
                    Notification::make()
                        ->title("Approve '{$record->name}' สำเร็จ")
                        ->success()
                        ->send();
                    $this->refreshFormData(['status']);
                })
                ->visible(fn () => $this->getRecord()->status === 'pending'),

            // ── Reject ────────────────────────────────────────────────────────
            Actions\Action::make('reject_character')
                ->label('Reject ตัวละคร')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label('เหตุผลที่ไม่ผ่าน')
                        ->helperText('ระบุให้ชัดเจนว่าด่านไหน หรือเกณฑ์ใดไม่ถึง — ผู้เล่นจะเห็นข้อความนี้และต้องทำแบบทดสอบ 3 ด่านใหม่ทั้งหมด')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    CharacterResource::rejectCharacter($record, $data['reason']);
                    Notification::make()
                        ->title("Reject '{$record->name}' แล้ว — แจ้งเหตุผลและรีเซ็ตด่านให้ทำใหม่แล้ว")
                        ->warning()
                        ->send();
                    $this->refreshFormData(['status']);
                })
                ->visible(fn () => $this->getRecord()->status === 'pending'),

            Actions\DeleteAction::make(),
        ];
    }
}
