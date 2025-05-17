<?php

namespace App\Filament\App\Widgets;

use App\Models\Distribution;
use App\Models\Site;
use App\Models\Delivery;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Facades\Filament;
use Livewire\Attributes\On;

class DistrictSitesCountWidget2 extends BaseWidget
{
    // Propriété pour stocker les filtres
    public ?array $filter = [];

    // Écouter l'événement pour mettre à jour les filtres
    #[On('updateFilters')]
    public function updateFilters($filter)
    {
        $this->filter = $filter;
    }

    protected function getStats(): array
    {
        $district = Filament::getTenant();
        $filters = $this->filter;

        // Base query pour les sites
        $sitesQuery = Site::query()->where('district_id', $district->id);

        // Base query pour les distributions
        $distributionsQuery = $district->distributions();

        // Appliquer le filtre par site si spécifié
        if (!empty($filters['site_id'])) {
            $sitesQuery->where('id', $filters['site_id']);
            $distributionsQuery->where('site_id', $filters['site_id']);
        }

        // Appliquer les filtres de date uniquement aux livraisons (delivery_date)
        if (!empty($filters['startDate']) || !empty($filters['endDate'])) {
            $deliveryIds = Distribution::query()
                ->when(!empty($filters['startDate']), function ($query) use ($filters) {
                    $query->whereDate('delivery_date', '>=', $filters['startDate']);
                })
                ->when(!empty($filters['endDate']), function ($query) use ($filters) {
                    $query->whereDate('delivery_date', '<=', $filters['endDate']);
                })
                ->pluck('id')
                ->toArray();

            // Filtrer les distributions qui sont liées à ces livraisons
            if (!empty($deliveryIds)) {
                $distributionsQuery->whereIn('delivery_id', $deliveryIds);
            } else {
                // Si aucune livraison ne correspond aux dates, ne renvoyer aucune distribution
                $distributionsQuery->whereRaw('1 = 0');
            }
        }

        // Préparer les descriptions selon les filtres
        $siteDescription = 'Sites appartenant à ce district';
        $distributionDescription = 'Distributions effectuées';

        if (!empty($filters['site_id'])) {
            $site = Site::find($filters['site_id']);
            $siteName = $site ? $site->name : 'sélectionné';
            $siteDescription = "Site $siteName";
            $distributionDescription = "Distributions sur $siteName";
        }

        if (!empty($filters['startDate']) || !empty($filters['endDate'])) {
            $dateRange = '';
            if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
                $dateRange = "du {$filters['startDate']} au {$filters['endDate']}";
            } elseif (!empty($filters['startDate'])) {
                $dateRange = "depuis le {$filters['startDate']}";
            } elseif (!empty($filters['endDate'])) {
                $dateRange = "jusqu'au {$filters['endDate']}";
            }

            $distributionDescription .= " ($dateRange)";
        }

        // Préparer les résultats pour les sites de la région
        $regionId = $district->region_id;
        $regionSitesQuery = Site::whereHas('district', function ($query) use ($regionId) {
            $query->where('region_id', $regionId);
        });

        // Compter les distributions en fonction des livraisons filtrées par date
        $distributionsCount = $distributionsQuery->count();

        return [
            Stat::make('Nombre de sites', $sitesQuery->count())
                ->description($siteDescription)
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('primary'),

            Stat::make('Total des distributions', $distributionsCount)
                ->description($distributionDescription)
                ->descriptionIcon('heroicon-o-truck')
                ->color('success'),

            Stat::make('Sites dans la région', $regionSitesQuery->count())
                ->description('Sites dans toute la région')
                ->descriptionIcon('heroicon-o-globe-alt')
                ->color('warning'),
        ];
    }
}
