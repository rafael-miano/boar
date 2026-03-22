<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Responses\LogoutResponse;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentAsset;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public $singletons = [
        \Filament\Http\Responses\Auth\Contracts\LoginResponse::class => \App\Http\Responses\LoginResponse::class,
        \Filament\Http\Responses\Auth\Contracts\LogoutResponse::class => LogoutResponse::class,
        \Filament\Http\Responses\Auth\Contracts\PasswordResetResponse::class => \App\Http\Responses\PasswordResetResponse::class,
        RegistrationResponseContract::class => \App\Http\Responses\RegistrationResponse::class,
    ];
    public function register(): void
    {
        // $this->app->singleton(
        //     \Filament\Http\Responses\Auth\Contracts\LogoutResponse::class,
        //     LogoutResponse::class
        // );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $isRtl = __('filament-panels::layout.direction') === 'rtl';

        FilamentAsset::register([
            Js::make('csrf-sync', resource_path('filament/csrf-sync.js')),
        ], 'app');

        FilamentIcon::register([
            'panels::sidebar.collapse-button' => $isRtl ? 'ri-arrow-left-double-fill' : 'ri-arrow-left-double-fill',
            'panels::sidebar.expand-button' => $isRtl ? 'ri-arrow-right-double-fill' : 'ri-arrow-right-double-fill',
            'pink' => Color::hex('#AD1457'),
        ]);

         Notifications::alignment(Alignment::End);
         Notifications::verticalAlignment(VerticalAlignment::End);
    }
}
