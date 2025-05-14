<?php

namespace App\Filament\App\Widgets;

use App\Models\Site;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Facades\Filament;

class DistrictSitesCountWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $district = Filament::getTenant();

        return [
            Stat::make('Nombre de sites', Site::where('district_id', $district->id)->count())
                ->description('Sites appartenant à ce district')
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('primary'),

            // Vous pouvez ajouter d'autres statistiques si vous le souhaitez
            // Par exemple, le nombre de distributions dans ce district
            Stat::make('Total des distributions', $district->distributions()->count())
                ->description('Distributions effectuées')
                ->descriptionIcon('heroicon-o-truck')
                ->color('success'),

            // Nombre moyen de sites par district dans la région
            Stat::make('Sites dans la région', function () use ($district) {
                $regionId = $district->region_id;
                $districtsCount = \App\Models\District::where('region_id', $regionId)->count();
                $sitesCount = Site::whereHas('district', function ($query) use ($regionId) {
                    $query->where('region_id', $regionId);
                })->count();

                return $sitesCount;
            })
                ->description('Sites dans toute la région')
                ->descriptionIcon('heroicon-o-globe-alt')
                ->color('warning'),
        ];
    }
}
