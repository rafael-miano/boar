<?php

namespace App\Filament\Widgets;

use App\Models\BoarReservation;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class ReservationOutcomeChart extends ChartWidget
{
    protected static ?string $heading = 'Reservation Outcomes';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected static ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $ownerId = auth()->id();

        $counts = BoarReservation::query()
            ->whereHas('boar', fn($query) => $query->where('user_id', $ownerId))
            ->selectRaw("reservation_status, COUNT(*) as total")
            ->whereIn('reservation_status', ['accepted', 'rejected'])
            ->groupBy('reservation_status')
            ->pluck('total', 'reservation_status');

        $accepted = (int) ($counts['accepted'] ?? 0);
        $rejected = (int) ($counts['rejected'] ?? 0);

        return [
            'datasets' => [
                [
                    'label' => 'Reservation Status',
                    'data' => [$accepted, $rejected],
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.8)', // accepted - green
                        'rgba(248, 113, 113, 0.85)', // rejected - red
                    ],
                    'borderColor' => [
                        'rgba(16, 185, 129, 1)',
                        'rgba(248, 113, 113, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'Accepted',
                'Rejected',
            ],
        ];
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                        },
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: (context) => {
                                const label = context.label ?? '';
                                const dataset = context.dataset?.data ?? [];
                                const total = dataset.reduce((sum, value) => sum + value, 0);
                                const value = context.raw ?? 0;
                                const percentage = total ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            },
                        },
                    },
                },
                animation: {
                    duration: 1600,
                    easing: 'easeOutBounce',
                },
                scales: {
                    x: {
                        display: false,
                    },
                    y: {
                        display: false,
                    },
                },
            }
        JS);
    }
}

