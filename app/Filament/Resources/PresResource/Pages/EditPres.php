<?php

namespace App\Filament\Resources\PresResource\Pages;

use App\Filament\Resources\PresResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPres extends EditRecord
{
    protected static string $resource = PresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
