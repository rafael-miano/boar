<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Http\Responses\Auth\RegistrationResponse as BaseRegistrationResponse;

class RegistrationResponse extends BaseRegistrationResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = $request->user() ?? auth()->user();

        if ($user && $user->role === 'admin') {
            return redirect()->route('filament.admin.pages.admin-dashboard');
        }

        if ($user && $user->role === 'boar-raiser') {
            return redirect()->route('filament.admin.pages.boar-raiser-dashboard');
        }

        if ($user && $user->role === 'customer') {
            if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
                if (method_exists($user, 'sendEmailVerificationNotification')) {
                    $user->sendEmailVerificationNotification();
                }
                return redirect()->route('filament.admin.auth.email-verification.prompt');
            }
            return redirect()->route('filament.customer.pages.customer-dashboard');
        }

        // Fallback to Filament's default post-registration behavior
        return parent::toResponse($request);
    }
}