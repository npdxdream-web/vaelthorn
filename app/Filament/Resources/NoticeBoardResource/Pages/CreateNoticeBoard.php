<?php

namespace App\Filament\Resources\NoticeBoardResource\Pages;

use App\Filament\Resources\NoticeBoardResource;
use App\Models\NoticeBoard;
use Filament\Resources\Pages\CreateRecord;

class CreateNoticeBoard extends CreateRecord
{
    protected static string $resource = NoticeBoardResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = NoticeBoard::max('sort_order') + 1;

        return $data;
    }
}
