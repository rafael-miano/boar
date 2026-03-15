<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BoarTypeChart;
use App\Filament\Widgets\BoarRaiserStatsOverview;
use App\Filament\Widgets\AdminReservationStatusChart;
use App\Filament\Widgets\AdminReservationsPerMonthChart;
use App\Filament\Widgets\ReservationOutcomeChart;
use App\Livewire\MyCalendarWidget;
use App\Models\BoarReservation;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;

class BoarRaiserDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-m-home';

    protected static string $view = 'filament.pages.boar-raiser-dashboard';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'boar-raiser';
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [
            BoarRaiserStatsOverview::class,
            BoarTypeChart::class,
            ReservationOutcomeChart::class,
            AdminReservationStatusChart::class,
            AdminReservationsPerMonthChart::class,
            MyCalendarWidget::class,
        ];
    }

    #[Computed]
    public function recentBirths()
    {
        return BoarReservation::query()
            ->whereHas('boar', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereNotNull('birth_confirmed_at')
            ->with(['boar', 'user'])
            ->latest('birth_confirmed_at')
            ->limit(5)
            ->get();
    }

    /** Needs boar raiser action: either pending your decision or accepted and to fulfill. */
    #[Computed]
    public function pendingServices()
    {
        return BoarReservation::query()
            ->whereHas('boar', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->where(function ($query) {
                $query->where('reservation_status', 'pending_boar_raiser')
                    ->orWhere(function ($q) {
                        $q->where('reservation_status', 'accepted')->where('service_status', 'pending');
                    });
            })
            ->with(['boar', 'user'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function unpaidPlatformFeeTotal(): float
    {
        return BoarReservation::unpaidPlatformFeeTotalForBoarRaiser(auth()->id());
    }
}
