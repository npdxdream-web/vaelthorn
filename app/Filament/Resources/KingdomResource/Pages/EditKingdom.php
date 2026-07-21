<?php

namespace App\Filament\Resources\KingdomResource\Pages;

use App\Filament\Resources\KingdomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKingdom extends EditRecord
{
    protected static string $resource = KingdomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
