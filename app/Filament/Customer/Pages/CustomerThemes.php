<?php

namespace App\Filament\Customer\Pages;

use Hasnayeen\Themes\Filament\Pages\Themes as BaseThemes;

class CustomerThemes extends BaseThemes
{
    // Use a unique slug to avoid any URI collision with the admin Themes page.
    protected static ?string $slug = 'customer-themes';

    protected static ?string $navigationLabel = 'Themes';
    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    // IMPORTANT: bypass the plugin’s canView() check (plugin isn’t registered on customer).
    public function mount(): void
    {
        // Intentionally empty: don't call parent::mount()
    }

    // Keep it out of the main sidebar if you prefer showing it in the user menu only.
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}