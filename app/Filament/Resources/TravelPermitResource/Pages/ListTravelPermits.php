<?php

namespace App\Filament\Resources\TravelPermitResource\Pages;

use App\Filament\Resources\TravelPermitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTravelPermits extends ListRecords
{
    protected static string $resource = TravelPermitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
