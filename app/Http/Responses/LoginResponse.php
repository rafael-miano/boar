<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $role = auth()->user()->role;

        if ($role === 'admin') {
            return redirect()->route('filament.admin.pages.admin-dashboard');
        }

        if ($role === 'boar-raiser') {
            return redirect()->route('filament.admin.pages.boar-raiser-dashboard');
        }

        if ($role === 'customer') {
            $user = auth()->user();

            if (method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
                return redirect()->route('filament.admin.auth.email-verification.prompt');
            }

            return redirect()->route('filament.customer.pages.customer-dashboard');
        }

        return parent::toResponse($request);
    }
}