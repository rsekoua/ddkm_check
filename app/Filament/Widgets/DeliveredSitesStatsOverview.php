<?php

namespace App\Filament\Widgets;

use App\Models\Site;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DeliveredSitesStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected $listeners = ['filter-updated' => 'updateStats'];

    public ?array $filters = [];

    public function mount(): void
    {
        $this->filters = [
            'district_id' => null,
            'startDate' => null,
            'endDate' => null,
        ];
    }

    public function updateStats(array $filters): void
    {
        $this->filters = $filters;
    }

    protected function getStats(): array
    {
        // Requête de base pour les sites
        $sitesQueryBase = Site::query();

        // Appliquer le filtre district_id s'il est défini
        if (!empty($this->filters['district_id'])) {
            $sitesQueryBase->where('district_id', $this->filters['district_id']);
        }

        // Compter le nombre total de sites correspondant au filtre de district
        $totalSitesCount = (clone $sitesQueryBase)->count();

        // Requête pour compter les sites livrés.
        // Un site est "livré" s'il a au moins une distribution avec une delivery_date non nulle,
        // et cette date tombe dans la période définie par les filtres startDate et endDate (s'ils sont actifs).
        $deliveredSitesQuery = clone $sitesQueryBase;

        $deliveredSitesQuery->whereHas('distributions', function ($query) {
            // S'assurer que la distribution a une date de livraison effective
            $query->whereNotNull('delivery_date');

            // Appliquer les filtres de date s'ils sont définis
            if (!empty($this->filters['startDate'])) {
                $query->whereDate('delivery_date', '>=', $this->filters['startDate']);
            }

            if (!empty($this->filters['endDate'])) {
                $query->whereDate('delivery_date', '<=', $this->filters['endDate']);
            }
        });

        $deliveredSitesCount = $deliveredSitesQuery->count();

        $percentageDelivered = ($totalSitesCount > 0) ? ($deliveredSitesCount / $totalSitesCount) * 100 : 0;

        // Déterminer la description pour la période filtrée
        $dateDescription = 'sur toute période';
        if (!empty($this->filters['startDate']) && !empty($this->filters['endDate'])) {
            $formattedStartDate = \Carbon\Carbon::parse($this->filters['startDate'])->translatedFormat('d M Y');
            $formattedEndDate = \Carbon\Carbon::parse($this->filters['endDate'])->translatedFormat('d M Y');
            $dateDescription = "du {$formattedStartDate} au {$formattedEndDate}";
        } elseif (!empty($this->filters['startDate'])) {
            $formattedStartDate = \Carbon\Carbon::parse($this->filters['startDate'])->translatedFormat('d M Y');
            $dateDescription = "depuis le {$formattedStartDate}";
        } elseif (!empty($this->filters['endDate'])) {
            $formattedEndDate = \Carbon\Carbon::parse($this->filters['endDate'])->translatedFormat('d M Y');
            $dateDescription = "jusqu'au {$formattedEndDate}";
        }

        $districtDescription = !empty($this->filters['district_id']) ? 'du district sélectionné' : 'de tous les districts';


        return [Stat::make('Total Sites', $totalSitesCount)
            ->description("Nombre total de sites {$districtDescription}")
            ->descriptionIcon('heroicon-m-squares-2x2')
            ->color('primary'),
            Stat::make('Sites Livrés', $deliveredSitesCount)
                ->description("Sites avec au moins une livraison {$dateDescription}")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Pourcentage de Livraison', number_format($percentageDelivered, 2) . '%')
                ->description("Sites livrés / Total sites {$districtDescription}")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($percentageDelivered >= 50 ? 'success' : ($percentageDelivered > 0 ? 'warning' : 'danger')),

        ];
    }
}
