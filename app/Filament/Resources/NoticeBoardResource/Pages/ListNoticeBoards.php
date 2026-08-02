<?php

namespace App\Filament\Resources\NoticeBoardResource\Pages;

use App\Filament\Resources\NoticeBoardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNoticeBoards extends ListRecords
{
    protected static string $resource = NoticeBoardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
