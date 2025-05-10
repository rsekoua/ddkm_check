<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();
        if(auth()->user()->isAdmin()) {
            return redirect()->to(Dashboard::getUrl(panel: 'admin'));
        }
        return parent::toResponse($request);
    }
}
