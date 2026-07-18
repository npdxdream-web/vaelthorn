<?php

namespace App\Filament\Resources\CharacterStatResource\Pages;

use App\Filament\Resources\CharacterStatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCharacterStats extends ListRecords
{
    protected static string $resource = CharacterStatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
