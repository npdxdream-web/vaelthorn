<?php

namespace App\Filament\Resources\KingdomResource\Pages;

use App\Filament\Resources\KingdomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKingdoms extends ListRecords
{
    protected static string $resource = KingdomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
