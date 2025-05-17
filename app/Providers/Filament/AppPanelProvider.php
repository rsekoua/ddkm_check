<?php

namespace App\Providers\Filament;

use App\Filament\App\Pages\Dashboard;
use App\Filament\App\Widgets\DistributionFilterWidget;
use App\Filament\App\Widgets\DistributionsTableWidget;
use App\Filament\App\Widgets\DistributionStatsWidget;
use App\Filament\App\Widgets\LatestDistributionsTableWidget;
use App\Filament\App\Widgets\LatestDistributionsWidget;
use App\Http\Middleware\TenantAccessMiddleware;
use App\Models\District;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->login()
            ->registration()
            ->passwordReset()
            ->profile()
            ->path('app')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->brandName('Gestion des distributions')
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
           // ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
           // ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
           // ->discoverWidgets(in: app_path('Filament/App/Widgets'))
           ->widgets([
               DistributionStatsWidget::class,
               LatestDistributionsTableWidget::class
           ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->tenant(
                District::class, slugAttribute: 'slug', ownershipRelationship: 'district'
            )
            ->tenantMenu(true)
            ->tenantMiddleware([
                TenantAccessMiddleware::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ;
    }
}
