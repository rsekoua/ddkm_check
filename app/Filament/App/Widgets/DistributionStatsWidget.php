<?php

namespace App\Filament\App\Widgets;

use App\Models\Distribution;
use App\Models\Site;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class DistributionStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    // Propriété pour stocker les filtres
    public $filterData = [
        'startDate' => null,
        'endDate' => null,
        'site_id' => null,
    ];

    // Utiliser l'attribut On pour écouter l'événement (syntaxe de Livewire 3)
    #[On('filter-updated')]
    public function updateFilter($filters): void
    {
        $this->filterData = $filters;
    }

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();

        $startDate = isset($this->filterData['startDate']) && $this->filterData['startDate']
            ? Carbon::parse($this->filterData['startDate'])
            : null;

        $endDate = isset($this->filterData['endDate']) && $this->filterData['endDate']
            ? Carbon::parse($this->filterData['endDate'])
            : null;

        $siteId = $this->filterData['site_id'] ?? null;

        // Nombre total de sites dans le district
        $totalSitesInDistrict = Site::query()
            ->when($tenant, function ($query) use ($tenant) {
                $query->where('district_id', $tenant->id);
            })
            ->count();

        // Base query pour les distributions
        $distributionQuery = Distribution::query()
            ->when($tenant, function (Builder $query) use ($tenant) {
                $query->whereHas('site', function (Builder $query) use ($tenant) {
                    $query->where('district_id', $tenant->id);
                });
            })
            ->when($siteId, function (Builder $query) use ($siteId) {
                $query->where('site_id', $siteId);
            })
            ->when($startDate, function (Builder $query) use ($startDate) {
                $query->whereDate('delivery_date', '>=', $startDate);
            })
            ->when($endDate, function (Builder $query) use ($endDate) {
                $query->whereDate('delivery_date', '<=', $endDate);
            });

        // Nombre de sites ayant reçu au moins une distribution selon les filtres
        $sitesWithDistributions = $distributionQuery
            ->select('site_id')
            ->distinct()
            ->count('site_id');

        // Préparation du label pour la période
        $dateRangeLabel = 'Toutes les sites du district';
        if ($startDate && $endDate) {
            $dateRangeLabel = 'Du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y');
        } elseif ($startDate) {
            $dateRangeLabel = 'Depuis le ' . $startDate->format('d/m/Y');
        } elseif ($endDate) {
            $dateRangeLabel = "Jusqu'au " . $endDate->format('d/m/Y');
        }

        $totalCount = Site::query()->where('district_id', $tenant->id)->count();

        // Calculer le pourcentage de sites livrés
        $sitelivrePercentage = $totalCount > 0
            ? round(($sitesWithDistributions / $totalCount) * 100, 1)
            : 0;


        return [
            Stat::make('Nombre des sites du district', $totalCount)
                // ->description($dateRangeLabel)
                ->description('Toutes les sites du district')
                ->color('primary'),
            Stat::make('Pourcentage de sites livrées', $sitelivrePercentage.'%')
                ->description($dateRangeLabel)
                //->description('Toutes les sites du district')
                ->color('success'),
            Stat::make('Nombre de site livrés', $sitesWithDistributions)
                 ->description($dateRangeLabel)
                //->description('Toutes les sites du district')
                ->color('primary'),


        ];
    }
}
