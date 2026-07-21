<?php

namespace App\Filament\Resources\TravelPermitResource\Pages;

use App\Filament\Resources\TravelPermitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTravelPermit extends EditRecord
{
    protected static string $resource = TravelPermitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
