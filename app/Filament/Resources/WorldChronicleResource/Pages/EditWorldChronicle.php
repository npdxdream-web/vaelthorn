<?php

namespace App\Filament\Resources\WorldChronicleResource\Pages;

use App\Filament\Resources\WorldChronicleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorldChronicle extends EditRecord
{
    protected static string $resource = WorldChronicleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
