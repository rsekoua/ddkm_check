<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Widgets\DistributionStatsWidget;
use App\Filament\App\Widgets\LatestDistributionsTableWidget; // Importer le nouveau widget de table
use App\Models\Site;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;
// Les imports suivants ne semblent pas utilisés et peuvent être enlevés si c'est le cas
// use Filament\Pages\Dashboard\Actions\FilterAction;
// use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
// use Illuminate\Contracts\View\View;
// use Laravel\Sail\Console\AddCommand;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    // Définir les propriétés pour stocker les valeurs de filtres
    public ?array $filter = [];
    // Remplacer le titre de la page pour y inclure le nom du district (tenant)
    public function getTitle(): string
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            return "District {$tenant->name}";
        }

        return "Tableau de bord";
    }


    // Méthode pour propager les changements de filtre aux widgets
    public function updatedFilter(): void
    {
        // Utiliser dispatch() au lieu de emit() dans Filament v3
        $this->dispatch('filter-updated', filters: $this->filter);
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        // Votre code existant pour les composants de formulaire...
                        Select::make('site_id')
                            ->label('Site')
                            ->options(function () {
                                $district = Filament::getTenant();
                                if (!$district) {
                                    return [];
                                }
                                return Site::query()->where('district_id', $district->id)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })->searchable()
                            ->placeholder('Tous les sites')
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->updatedFilter();
                            }),
                        DatePicker::make('startDate')
                            ->maxDate(function (Get $get) {
                                $endDate = $get('endDate');
                                return $endDate ?: now();
                            })
                            ->live()
                            ->native(false)
                            ->hint('Filtre par date de livraison')
                            ->afterStateUpdated(function () {
                                $this->updatedFilter();
                            }),
                        DatePicker::make('endDate')
                            ->minDate(function (Get $get) {
                                return $get('startDate');
                            })
                            ->maxDate(now())
                            ->afterStateUpdated(function (Get $get, $state, callable $set) {
                                $startDate = $get('startDate');
                                if ($state && $startDate && $startDate > $state) {
                                    $set('startDate', $state);
                                }
                                $this->updatedFilter();
                            })
                            ->live()
                            ->native(false)
                            ->hint('Filtre par date de livraison'),
                    ])
                    ->columns(3),
            ])
            ->statePath('filter');
    }

    // Hook pour le cycle de vie Livewire
    public function booted(): void
    {
        // S'assurer que les filtres sont initialisés et transmis lors du chargement initial
        if (empty($this->filter) && method_exists($this, 'filtersForm')) {
            // Initialiser avec les valeurs par défaut du formulaire si $filter est vide.
            $this->filter = $this->filtersForm(Form::make())->getState();
        }
        // Utiliser dispatch() au lieu de emit() dans Filament v3
        $this->dispatch('filter-updated', filters: $this->filter);
    }
}
