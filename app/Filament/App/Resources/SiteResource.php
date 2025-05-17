<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SiteResource\Pages;
use App\Filament\App\Resources\SiteResource\RelationManagers;
use App\Models\Site;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    // Filament 3 utilise cette méthode pour le label de navigation
    public static function getNavigationLabel(): string
    {
        return __('Sites');
    }

    // Méthode pour ajouter un badge au label de navigation
    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();

        if (!$tenant) {
            return null;
        }

        return (string) Site::query()
            ->where('district_id', $tenant->id)
            ->count();
    }

    // Définir la couleur du badge (options: primary, secondary, success, warning, danger, gray)
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    // Désactive la pluralisation (syntaxe Filament 3)
    public static function getPluralModelLabel(): string
    {
        return __('Sites');
    }

    // Pour la cohérence des labels au singulier également
    public static function getModelLabel(): string
    {
        return __('Site');
    }



    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('district_id')
                    ->relationship('district', 'name')
                    ->default(fn () => Filament::getTenant()?->id)
                    ->disabled('edit')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->maxLength(255),
                Forms\Components\TextInput::make('contact_info')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('district.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Site mise à jour')
                            ->body('Le site a été mise à jour avec succès.')
                            ->icon('heroicon-o-check-circle')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DistributionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}
