<?php

namespace App\Filament\App\Widgets;

use App\Models\Distribution;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Carbon\Carbon;
use Livewire\Attributes\On;

class LatestDistributionsTableWidget extends BaseWidget
{
    protected static ?int $sort = 2; // S'assure qu'il s'affiche après DistributionStatsWidget

    protected int | string | array $columnSpan = 'full'; // Pour que la table occupe toute la largeur

    // Propriété pour stocker les filtres
    public $filterData = [
        'startDate' => null,
        'endDate' => null,
        'site_id' => null,
    ];

    // Écouter l'événement filter-updated émis par la page Dashboard
    #[On('filter-updated')]
    public function updateFilter($filters): void
    {
        $this->filterData = $filters;
        // La table se mettra à jour automatiquement car la requête getTableQuery() sera ré-évaluée.
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $tenant = Filament::getTenant();

                $startDate = isset($this->filterData['startDate']) && $this->filterData['startDate']
                    ? Carbon::parse($this->filterData['startDate'])
                    : null;

                $endDate = isset($this->filterData['endDate']) && $this->filterData['endDate']
                    ? Carbon::parse($this->filterData['endDate'])
                    : null;

                $siteId = $this->filterData['site_id'] ?? null;

                $query = Distribution::query()
                    ->with('site') // Pré-charger la relation 'site' pour l'affichage du nom
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
                    })
                    ->latest('delivery_date'); // Trier par date de livraison la plus récente

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('site.name')
                    ->label('Site')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Date de Livraison')
                    ->date('d/m/Y') // Formate la date pour l'affichage
                    ->sortable(),
                Tables\Columns\TextColumn::make('deliveryType.name')
                    ->numeric()
                    ->sortable(),

            ])
            ->defaultSort('delivery_date', 'desc'); // Trier par défaut les distributions les plus récentes en premier
    }

    protected function getTableHeading(): string
    {
        return 'Dernières Distributions';
    }
}
