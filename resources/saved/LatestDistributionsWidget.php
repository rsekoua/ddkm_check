<?php

namespace App\Filament\App\Widgets;

use App\Models\Distribution;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Facades\Filament;

class LatestDistributionsWidget2 extends BaseWidget
{
//    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getFilterData(): array
    {
        return $this->getLivewire()->getWidgetData()['filter'] ?? [];
    }


    public function table(Table $table): Table
    {
        // Récupérer le district actuel (tenant)
        $district = Filament::getTenant();

        return $table
            ->heading('Dernières distributions du district ' . $district->name)
            ->query(
                Distribution::query()
                    ->where('district_id', $district->id) // Filtrer par le district actuel
                    ->with(['site', 'site.district', 'deliveryType'])
                    ->latest('delivery_date')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('site.name')
                    ->label('Site')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('site.district.name')
                    ->label('District')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deliveryType.name')
                    ->label('Type de livraison')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}
