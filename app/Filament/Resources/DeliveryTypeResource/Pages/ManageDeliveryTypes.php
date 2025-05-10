<?php

namespace App\Filament\Resources\DeliveryTypeResource\Pages;

use App\Filament\Resources\DeliveryTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDeliveryTypes extends ManageRecords
{
    protected static string $resource = DeliveryTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
