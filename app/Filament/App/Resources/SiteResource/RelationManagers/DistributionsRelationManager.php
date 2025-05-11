<?php

namespace App\Filament\App\Resources\SiteResource\RelationManagers;

use App\Models\Distribution;
use App\Models\Site;
use App\Rules\UniqueMonthlyDistributionRule;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;



class DistributionsRelationManager extends RelationManager
{
    protected static string $relationship = 'distributions';

    // Cette méthode intercepte la création d'un enregistrement
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (ValidationException $e) {
            // Transférer les erreurs au formulaire Filament
            $this->form->addValidationErrors($e->errors());
            $this->halt();

            // Cette ligne ne sera jamais atteinte car halt() arrête l'exécution
            return new Distribution();
        }
    }
// Cette méthode intercepte la mise à jour d'un enregistrement
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (ValidationException $e) {
            // Transférer les erreurs au formulaire Filament
            $this->form->addValidationErrors($e->errors());
            $this->halt();

            // Cette ligne ne sera jamais atteinte car halt() arrête l'exécution
            return $record;
        }
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('district_id')
                    ->disabled()
                    ->default(fn () => Filament::getTenant()?->id)
                    ->relationship('district', 'name'),

                Forms\Components\Select::make('site_id')
                    ->relationship('site', 'name')
                    ->default($this->ownerRecord->id)
                    ->afterStateUpdated(fn (callable $set) => $set('district_id', Site::query()->find(request()->site_id)?->district_id ?? null))
                    ->disabled()
                    ->required(),
                Forms\Components\Select::make('delivery_type_id')
                    ->relationship('deliveryType', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('delivery_date')
                    ->required()
                    ->date()
                    ->maxDate(now())
                    ->native(false)
                    ->format('d/m/Y')
                    ->rules([
                        function (Forms\Get $get, $record) {
                            $siteId = $get('site_id');
                            $deliveryTypeId = $get('delivery_type_id');
                            $distributionId = $record ? $record->id : null;

                            return new UniqueMonthlyDistributionRule(
                                $siteId,
                                $deliveryTypeId,
                                $distributionId
                            );
                        },
                    ])

                    ->reactive(),
                Forms\Components\Textarea::make('difficulties')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('solutions')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ])->columns(3)
            ;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('delivery_date')
            ->columns([
                Tables\Columns\TextColumn::make('site.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deliveryType.name')
                    ->label('Type de livraison')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
