<?php

namespace App\Filament\Resources\NoticeBoardResource\Pages;

use App\Filament\Resources\NoticeBoardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNoticeBoard extends EditRecord
{
    protected static string $resource = NoticeBoardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
