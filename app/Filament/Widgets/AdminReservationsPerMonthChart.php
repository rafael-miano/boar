<?php

namespace App\Filament\Widgets;

use App\Models\BoarReservation;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AdminReservationsPerMonthChart extends ChartWidget
{
    protected static ?string $heading = 'Reservations per month';

    protected static ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $start = Carbon::now()->subMonths(5)->startOfMonth();

        $rows = BoarReservation::query()
            ->whereHas('boar', fn ($q) => $q->where('user_id', auth()->id()))
            ->selectRaw('DATE_FORMAT(service_date, "%Y-%m") as ym, COUNT(*) as total')
            ->whereDate('service_date', '>=', $start)
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->startOfMonth();
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = (int) ($rows[$key] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Reservations',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                    'borderColor' => 'rgba(37, 99, 235, 1)',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                    'maxBarThickness' => 40,
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
                        display: false,
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                    y: {
                        beginAtZero: true,
                    },
                },
            }
        JS);
    }
}

