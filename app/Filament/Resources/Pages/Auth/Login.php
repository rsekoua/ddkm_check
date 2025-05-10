<?php

namespace App\Filament\Resources\Pages\Auth;

use Filament\Pages\Auth\Login as BasePage;

class Login extends BasePage
{
    public function getTitle(): string
    {
        return 'Administration';
    }

    protected function getFormActions(): array
    {
        $actions = parent::getFormActions();

        // Ajouter un lien vers le panel app
        $actions[] = \Filament\Actions\Action::make('app_login')
            ->label('Se connecter au panel utilisateur')
            ->url(route('filament.app.auth.login'))
            ->color('secondary');

        return $actions;
    }
}
