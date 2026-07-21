<?php

namespace App\Filament\Resources\TravelPermitResource\Pages;

use App\Filament\Resources\TravelPermitResource;
use App\Models\Inventory;
use App\Models\Item;
use Filament\Resources\Pages\CreateRecord;

class CreateTravelPermit extends CreateRecord
{
    protected static string $resource = TravelPermitResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $item = Item::create([
            'name'         => $data['item_name'],
            'type'         => 'permit',
            'rarity'       => 'common',
            'description'  => 'ใบอนุญาตข้ามเขตแดน — แอดมินมอบให้เท่านั้น ไม่สามารถซื้อขายได้',
            'is_tradeable' => false,
            'is_active'    => true,
        ]);

        Inventory::firstOrCreate(
            ['character_id' => $data['character_id'], 'item_id' => $item->id],
            ['quantity' => 1]
        );

        $data['item_id']    = $item->id;
        $data['granted_by'] = auth()->id();
        unset($data['item_name']);

        return $data;
    }
}
