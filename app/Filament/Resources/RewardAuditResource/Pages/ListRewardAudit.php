<?php

namespace App\Filament\Resources\RewardAuditResource\Pages;

use App\Filament\Resources\RewardAuditResource;
use Filament\Resources\Pages\ListRecords;

class ListRewardAudit extends ListRecords
{
    protected static string $resource = RewardAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
