<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        \Filament\Http\Responses\Auth\Contracts\LoginResponse::class => \App\Http\Responses\LoginResponse::class,
        \Filament\Http\Responses\Auth\Contracts\LogoutResponse::class => \App\Http\Responses\LogoutResponse::class,
    ];
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
