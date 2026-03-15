<?php

namespace App\Filament\Widgets;

use App\Models\BoarReservation;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Platform overview';

    protected function getStats(): array
    {
        $totalBoarRaisers = User::where('role', 'boar-raiser')->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalReservations = BoarReservation::count();

        $totalPlatformFees = (float) BoarReservation::query()
            ->whereNotNull('platform_fee')
            ->sum('platform_fee');

        $unpaidPlatformFees = (float) BoarReservation::query()
            ->whereNotNull('platform_fee')
            ->whereNull('platform_fee_paid_at')
            ->sum('platform_fee');

        $paidPlatformFees = (float) BoarReservation::query()
            ->whereNotNull('platform_fee')
            ->whereNotNull('platform_fee_paid_at')
            ->sum('platform_fee');

        return [
            Stat::make('Boar raisers', (string) $totalBoarRaisers)
                ->description('Registered boar raiser accounts')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Customers', (string) $totalCustomers)
                ->description('Registered customer accounts')
                ->icon('heroicon-o-user-circle')
                ->color('success'),

            Stat::make('Total reservations', (string) $totalReservations)
                ->description('All boar service reservations')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('info'),

            Stat::make('Platform fees (generated)', '₱' . number_format($totalPlatformFees, 2))
                ->description('Total platform fees from confirmed money payments')
                ->icon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make('Platform fees (unpaid)', '₱' . number_format($unpaidPlatformFees, 2))
                ->description('Still to be settled by boar raisers')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($unpaidPlatformFees > 0 ? 'danger' : 'success'),

            Stat::make('Platform fees (paid)', '₱' . number_format($paidPlatformFees, 2))
                ->description('Already remitted to the platform')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}

