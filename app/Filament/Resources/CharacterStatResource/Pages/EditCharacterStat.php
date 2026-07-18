<?php

namespace App\Filament\Resources\CharacterStatResource\Pages;

use App\Filament\Resources\CharacterStatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCharacterStat extends EditRecord
{
    protected static string $resource = CharacterStatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
