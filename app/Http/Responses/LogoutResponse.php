<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\LogoutResponse as BaseLogoutResponse;
use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;

class LogoutResponse extends BaseLogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        if(Filament::getCurrentPanel()->getId() === 'admin')
        {
            return redirect()->to('/app/login');
        }
        return parent::toResponse($request);
    }
}
