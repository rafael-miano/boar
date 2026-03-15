<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Register;
use App\Filament\Widgets\BoarTypeChart;
use App\Filament\Widgets\ReservationOutcomeChart;
use App\Http\Middleware\RedirectToProperPanelMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\MenuItem;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->registration(Register::class)
            ->emailVerification()
            ->passwordReset()
            ->colors([
                'primary' => Color::Pink,
            ])
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->simpleProfilePage(false)
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Edit Profile')
                    ->icon('heroicon-m-user')
                    ->url(fn () => route(\App\Filament\Pages\Auth\EditProfile::getRouteName('admin'))),
            ])
            ->brandLogo(fn() => view('filament.components.logo'))
            ->darkModeBrandLogo(fn() => view('filament.components.dark-mode-logo'))
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->plugin(
                \Hasnayeen\Themes\ThemesPlugin::make()
            )
            ->spa()
            ->resources([
                \App\Filament\Resources\PlatformFeeSettlementResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                BoarTypeChart::class,
                ReservationOutcomeChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetTheme::class
            ])
            ->authMiddleware([
                Authenticate::class,
                RedirectToProperPanelMiddleware::class
            ]);
    }
}
