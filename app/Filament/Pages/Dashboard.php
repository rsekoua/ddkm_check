<?php
namespace App\Filament\Pages;

use App\Filament\Widgets\DeliveredSitesStatsOverview;
use App\Models\District;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    // Propriété pour stocker les valeurs des filtres
    public ?array $filter = [];

    // Définir le titre de la page
    public function getTitle(): string
    {
        return "Tableau de bord Administrateur";
    }

    // Méthode pour propager les changements de filtre aux widgets
    public function updatedFilter(): void
    {
        $this->dispatch('filter-updated', filters: $this->filter);
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('district_id')
                            ->label('District')
                            ->options(
                                District::query()
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->placeholder('Tous les districts')
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->updatedFilter();
                            }),
                        DatePicker::make('startDate')
                            ->label('Date de début')
                            ->maxDate(function (Get $get) {
                                $endDate = $get('endDate');
                                return $endDate ?: now();
                            })
                            ->live()
                            ->native(false)
                            ->hint('Filtrer par date')
                            ->afterStateUpdated(function () {
                                $this->updatedFilter();
                            }),
                        DatePicker::make('endDate')
                            ->label('Date de fin')
                            ->minDate(function (Get $get) {
                                return $get('startDate');
                            })
                            ->maxDate(now())
                            ->afterStateUpdated(function (Get $get, $state, callable $set) {
                                $startDate = $get('startDate');
                                // Si la date de fin est définie et antérieure à la date de début,
                                // ajuster la date de début pour qu'elle soit égale à la date de fin.
                                if ($state && $startDate && $startDate > $state) {
                                    $set('startDate', $state);
                                }
                                $this->updatedFilter();
                            })
                            ->live()
                            ->native(false)
                            ->hint('Filtrer par date'),
                    ])
                    ->columns(3),
            ])
            ->statePath('filter'); // Important pour lier le formulaire à la propriété $filter
    }

    // Hook pour le cycle de vie Livewire, exécuté lorsque le composant est initialisé
    public function booted(): void
    {
        // S'assurer que les filtres sont initialisés avec les valeurs par défaut du formulaire
        // et transmis lors du chargement initial.
        if (empty($this->filter) && method_exists($this, 'filtersForm')) {
            // Initialiser avec les valeurs par défaut (null pour chaque champ)
            $this->filter = $this->filtersForm(Form::make())->getState();
        }
        // Dispatch l'événement initial pour que les widgets se chargent avec les filtres par défaut (ou vides)
        $this->dispatch('filter-updated', filters: $this->filter);
    }


}
