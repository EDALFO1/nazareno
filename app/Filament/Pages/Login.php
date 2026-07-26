<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Hidden;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    /**
     * Sin "Acuérdate de mí": la sesión nunca debe sobrevivir el cierre del
     * navegador ni prolongarse más allá de lo que dure la sesión normal.
     */
    protected function getRememberFormComponent(): Component
    {
        return Hidden::make('remember')
            ->default(false);
    }
}
