<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\DistributionResource\Pages;
use App\Filament\App\Resources\DistributionResource\RelationManagers;
use App\Models\Distribution;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DistributionResource extends Resource
{
    protected static ?string $model = Distribution::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\Select::make('site_id')
                        ->label('Etablissement sanitaire')
                        ->relationship('site', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\DatePicker::make('delivery_date')
                        ->label('Date de livraison')
                        ->date()
                        ->maxDate(now())
                        ->native(false)
                        ->required(),
                    Forms\Components\Select::make('delivery_type_id')
                        ->native(false)
                        ->preload()
                        ->relationship('deliveryType', 'name')
                        ->required(),

                ])->columns('3'),
                 Forms\Components\Textarea::make('difficulties')
                     ->columnSpanFull(),
                    Forms\Components\Textarea::make('solutions')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')
                        ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('district.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('site.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deliveryType.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->dateTime()
                    ->sortable(),

            ])

            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDistributions::route('/'),
        ];
    }
}
