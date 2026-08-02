<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createThreadFromEvent')
                ->label('สร้างกระทู้ผูก Event นี้')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                // Threading through an inactive Event is pointless — the create-thread
                // dropdown (phase 1) only ever lists active Events anyway.
                ->visible(fn (): bool => $this->record->status === 'active')
                ->action(function () {
                    $city = EventResource::resolveThreadTargetCity($this->record);

                    if (! $city) {
                        Notification::make()
                            ->title('ไม่พบเมืองให้สร้างกระทู้')
                            ->body('ต้องมีอย่างน้อย 1 เมืองในระบบก่อนถึงจะสร้างกระทู้ผูก Event ได้')
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->redirect(route('thread.create', ['id' => $city->id, 'event_id' => $this->record->id]));
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
