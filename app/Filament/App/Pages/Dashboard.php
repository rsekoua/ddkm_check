<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Widgets\DistrictSitesCountWidget;
use App\Filament\App\Widgets\LatestDistributionsWidget;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{


    // Vous pouvez personnaliser votre dashboard ici si nécessaire
    public function getWidgets(): array
    {
        return [
            DistrictSitesCountWidget::class,
            LatestDistributionsWidget::class,
        ];
    }

}
