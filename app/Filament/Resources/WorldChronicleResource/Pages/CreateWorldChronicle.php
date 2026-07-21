<?php

namespace App\Filament\Resources\WorldChronicleResource\Pages;

use App\Filament\Resources\WorldChronicleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorldChronicle extends CreateRecord
{
    protected static string $resource = WorldChronicleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['generated_at'] = now();

        return $data;
    }
}
