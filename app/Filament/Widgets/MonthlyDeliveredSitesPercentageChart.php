<?php

namespace App\Filament\Widgets;

use App\Models\Site;
use App\Models\Distribution;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class MonthlyDeliveredSitesPercentageChart extends ChartWidget
{
    protected static ?string $heading = 'Pourcentage de Sites Livrés par Mois';

    //protected static ?int $sort = 1;

   // protected int|string|array $columnSpan = 'full';

    // Propriété pour stocker les filtres reçus du tableau de bord
    public ?array $filters = null;

    // Listener pour l'événement émis par le tableau de bord
    protected function getListeners(): array
    {
        return [
            'filter-updated' => 'applyFilters',
        ];
    }

    // Méthode pour appliquer les filtres et rafraîchir le widget
    public function applyFilters(array $filters): void
    {
        $this->filters = $filters;
        // Il n'est généralement pas nécessaire de forcer le rechargement des données ici,
        // Filament devrait réévaluer getData() lorsque les propriétés publiques changent
        // et que le composant est rafraîchi.
        // Cependant, si le graphique ne se met pas à jour, vous pourriez avoir besoin de
        // $this->dispatch('$refresh'); ou d'une méthode pour explicitement recharger les données.
    }


    protected function getData(): array
    {
        $labels = [];
        $data = [];
        $filterDistrictId = $this->filters['district_id'] ?? null;
        $filterStartDate = isset($this->filters['startDate']) ? Carbon::parse($this->filters['startDate']) : null;
        $filterEndDate = isset($this->filters['endDate']) ? Carbon::parse($this->filters['endDate']) : null;

        // Déterminer la période
        $startDate = Carbon::now()->subMonths(11)->startOfMonth(); // Défaut : 12 derniers mois
        $endDate = Carbon::now()->endOfMonth();

        if ($filterStartDate && $filterEndDate) {
            $startDate = $filterStartDate->copy()->startOfMonth();
            $endDate = $filterEndDate->copy()->endOfMonth();
        } elseif ($filterStartDate) {

            $startDate = $filterStartDate->copy()->startOfMonth();
            $endDate = $filterStartDate->copy()->addMonths(11)->endOfMonth();
        }

        if ($startDate->greaterThan($endDate)) {
            // Option 1: Retourner un graphique vide
            // return [
            //     'datasets' => [['label' => 'Sites Livrés (%)', 'data' => []]],
            //     'labels' => []
            // ];
            // Option 2: Inverser pour au moins afficher le mois de startDate
            $endDate = $startDate->copy()->endOfMonth();
        }

        $period = CarbonPeriod::create($startDate, '1 month', $endDate);

        // Utiliser l'ID du district du filtre du tableau de bord s'il est fourni, sinon celui du tenant
        $activeDistrictId = $filterDistrictId ?? Filament::getTenant()?->id;

        foreach ($period as $date) {
            $year = $date->year;
            $month = $date->month;
            $monthName = $date->translatedFormat('M Y');
            $labels[] = $monthName;

            $totalSitesQuery = Site::query();
            if ($activeDistrictId) {
                $totalSitesQuery->where('district_id', $activeDistrictId);
            }
            $totalSitesInScope = $totalSitesQuery->count();

            if ($totalSitesInScope === 0) {
                $data[] = 0;
                continue;
            }

            $deliveredSitesCountQuery = Distribution::query()
                ->whereNotNull('delivery_date')
                ->whereYear('delivery_date', $year)
                ->whereMonth('delivery_date', $month);

            if ($activeDistrictId) {
                $deliveredSitesCountQuery->whereHas('site', function (Builder $query) use ($activeDistrictId) {
                    $query->where('district_id', $activeDistrictId);
                });
            }
            $deliveredSitesInMonth = $deliveredSitesCountQuery->distinct()->count('site_id');

            $percentage = round(($deliveredSitesInMonth / $totalSitesInScope) * 100, 2);
            $data[] = $percentage;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sites Livrés (%)',
                    'data' => $data,
                    'borderColor' => '#4ade80',
                    'backgroundColor' => 'rgba(74, 222, 128, 0.2)',
                    'tension' => 0.1,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
