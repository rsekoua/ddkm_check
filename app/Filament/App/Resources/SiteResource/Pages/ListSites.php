<?php

namespace App\Filament\App\Resources\SiteResource\Pages;

use App\Filament\App\Resources\SiteResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListSites extends ListRecords
{
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getHeading(): string|Htmlable
    {
        $tenant = Filament::getTenant();
        if ($tenant) {
            // Supposant que votre modèle District a un attribut 'name'
            // et que getFilamentName() est disponible pour le formatage souhaité.
            // Si getFilamentName() n'existe pas ou si vous préférez le nom simple :
            // return __('Sites du District: :districtName', ['districtName' => $tenant->name]);
            return __(':districtName', ['districtName' => $tenant->getFilamentName()]);
        }

        return parent::getHeading(); // Titre par défaut si aucun district n'est sélectionné
    }
}
