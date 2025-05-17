<?php

namespace App\Filament\App\Resources\DistributionResource\Pages;

use App\Filament\App\Resources\DistributionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDistributions extends ManageRecords
{
    protected static string $resource = DistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
