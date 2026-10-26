<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    protected array $extraBodyAttributes = [
        'class' => 'nibiru-login-page',
    ];
}
