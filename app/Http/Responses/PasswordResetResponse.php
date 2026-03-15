<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\PasswordResetResponse as BasePasswordResetResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class PasswordResetResponse extends BasePasswordResetResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect()->route('login');
    }
}

