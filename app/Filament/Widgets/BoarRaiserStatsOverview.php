<?php

namespace App\Filament\Widgets;

use App\Models\BoarReservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BoarRaiserStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Your reservations overview';

    protected function getStats(): array
    {
        $userId = auth()->id();

        $query = BoarReservation::query()
            ->whereHas('boar', fn ($q) => $q->where('user_id', $userId));

        $totalReservations = (clone $query)->count();

        $activeReservations = (clone $query)
            ->whereIn('reservation_status', ['pending', 'pending_boar_raiser', 'accepted', 'confirmed'])
            ->count();

        $completedServices = (clone $query)
            ->where('service_status', 'completed')
            ->count();

        return [
            Stat::make('Total reservations', (string) $totalReservations)
                ->description('All reservations involving your boars')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary'),

            Stat::make('Active reservations', (string) $activeReservations)
                ->description('Pending, accepted, or confirmed')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Completed services', (string) $completedServices)
                ->description('Services marked as completed')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}

