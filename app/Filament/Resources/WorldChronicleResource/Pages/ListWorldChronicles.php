<?php

namespace App\Filament\Resources\WorldChronicleResource\Pages;

use App\Filament\Resources\WorldChronicleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorldChronicles extends ListRecords
{
    protected static string $resource = WorldChronicleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
