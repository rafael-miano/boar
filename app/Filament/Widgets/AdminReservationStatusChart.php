<?php

namespace App\Filament\Widgets;

use App\Models\BoarReservation;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class AdminReservationStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Reservations by status';

    protected static ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $userId = auth()->id();

        $counts = BoarReservation::query()
            ->whereHas('boar', fn ($q) => $q->where('user_id', $userId))
            ->selectRaw('reservation_status, COUNT(*) as total')
            ->groupBy('reservation_status')
            ->pluck('total', 'reservation_status');

        $statuses = [
            'pending',
            'pending_boar_raiser',
            'accepted',
            'confirmed',
            'rejected',
        ];

        $labels = [
            'Pending (customer)',
            'Pending (boar raiser)',
            'Accepted',
            'Confirmed (paid)',
            'Rejected',
        ];

        $data = [];
        foreach ($statuses as $status) {
            $data[] = (int) ($counts[$status] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Reservations',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.85)',   // pending - amber
                        'rgba(59, 130, 246, 0.85)',   // pending boar raiser - blue
                        'rgba(16, 185, 129, 0.85)',   // accepted - green
                        'rgba(34, 197, 94, 0.9)',     // confirmed - deeper green
                        'rgba(248, 113, 113, 0.9)',   // rejected - red
                    ],
                    'borderColor' => [
                        'rgba(251, 191, 36, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(248, 113, 113, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
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
            }
        JS);
    }
}

