<?php

namespace App\Filament\Resources\KingdomResource\Pages;

use App\Filament\Resources\KingdomResource;
use App\Models\Kingdom;
use Filament\Resources\Pages\CreateRecord;

class CreateKingdom extends CreateRecord
{
    protected static string $resource = KingdomResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = Kingdom::max('sort_order') + 1;

        return $data;
    }
}
