<?php

namespace App\Filament\Resources\PresResource\Pages;

use App\Filament\Resources\PresResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPres extends ListRecords
{
    protected static string $resource = PresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
