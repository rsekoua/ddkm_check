<?php

namespace App\Filament\App\Filters;

use Livewire\Attributes\Persistent;

class DashboardFilters
{
    #[Persistent]
    public ?string $site_id = null;

    #[Persistent]
    public ?string $start_date = null;

    #[Persistent]
    public ?string $end_date = null;

    public function apply($query)
    {
        // Filtre par site si sélectionné
        if ($this->site_id) {
            $query->where('site_id', $this->site_id);
        }

        // Filtre par dates si définies
        if ($this->start_date) {
            $query->whereDate('delivery_date', '>=', $this->start_date);
        }

        if ($this->end_date) {
            $query->whereDate('delivery_date', '<=', $this->end_date);
        }

        return $query;
    }

    public function reset(): void
    {
        $this->site_id = null;
        $this->start_date = null;
        $this->end_date = null;
    }
}
