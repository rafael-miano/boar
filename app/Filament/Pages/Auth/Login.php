<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/login.form.password.label'))
            ->hint(
                filament()->hasPasswordReset()
                    ? new HtmlString(
                        Blade::render(
                            '<x-filament::link :href="route(\'password-reset.request\')" tabindex="3">
                                {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}
                            </x-filament::link>'
                        ),
                    )
                    : null,
            )
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }
}

