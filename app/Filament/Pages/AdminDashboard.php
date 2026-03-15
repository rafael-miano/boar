<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStatsOverview;
use Filament\Pages\Page;

class AdminDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-m-home';

    protected static string $view = 'filament.pages.admin-dashboard';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdminStatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
        ];
    }
}
