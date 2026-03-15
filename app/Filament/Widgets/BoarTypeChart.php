<?php

namespace App\Filament\Widgets;

use App\Models\Boar;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BoarTypeChart extends ChartWidget
{
    protected static ?string $heading = 'Boars by Breed';

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $ownerId = auth()->id();

        $counts = Boar::query()
            ->where('user_id', $ownerId)
            ->selectRaw('COALESCE(NULLIF(TRIM(boar_type), \'\'), \'Unspecified\') AS type, COUNT(*) AS total')
            ->groupBy('type')
            ->orderBy('type')
            ->pluck('total', 'type');

        $types = $counts->keys()->values();
        $labels = $types->map(fn ($type) => Str::title($type));

        $colors = $this->generateColors($counts->count());

        foreach ($types as $index => $type) {
            $typeKey = Str::lower($type);

            if ($typeKey === 'duroc') {
                $colors[$index] = '#f59e0b';
                continue;
            }

            if ($typeKey === 'pietrain') {
                $colors[$index] = '#6366f1';
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Boars per Breed',
                    'data' => $counts->values()->all(),
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
                    'hoverBackgroundColor' => $colors,
                    'barPercentage' => 1.0,
                    'categoryPercentage' => 1.0,
                    'barThickness' => null,
                    'borderRadius' => 6,
                    'maxBarThickness' => 48,
                ],
            ],
            'labels' => $labels->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            (() => {
                const isDarkModeEnabled = () => {
                    const root = document.documentElement;
                    const datasetTheme = root.dataset?.theme ?? root.getAttribute('data-theme');

                    if (root.classList.contains('dark')) {
                        return true;
                    }

                    if (typeof datasetTheme === 'string' && datasetTheme.toLowerCase() === 'dark') {
                        return true;
                    }

                    return false;
                };

                const getTextColor = () => (isDarkModeEnabled() ? '#e5e7eb' : '#1f2937');
                const getLegendColor = () => (isDarkModeEnabled() ? '#d1d5db' : '#4b5563');
                const getGridColor = () => (isDarkModeEnabled() ? 'rgba(229, 231, 235, 0.15)' : 'rgba(55, 65, 81, 0.08)');

                return {
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: getLegendColor(),
                                padding: 12,
                                usePointStyle: false,
                                generateLabels: (chart) => {
                                    const dataset = chart.data.datasets[0] ?? {};
                                    const colors = Array.isArray(dataset.backgroundColor) ? dataset.backgroundColor : [];

                                    return chart.data.labels.map((label, index) => ({
                                        text: label,
                                        fillStyle: colors[index] ?? '#9ca3af',
                                        strokeStyle: colors[index] ?? '#9ca3af',
                                        hidden: !chart.getDataVisibility(index),
                                        index,
                                    }));
                                },
                            },
                            onClick: (event, legendItem, legend) => {
                                const index = legendItem.index;
                                legend.chart.toggleDataVisibility(index);
                                legend.chart.update();
                            },
                        },
                        tooltip: {
                            enabled: true,
                        },
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutBounce',
                    },
                    scales: {
                        y: {
                            title: {
                                display: true,
                                text: 'Number of Boars',
                                color: getTextColor(),
                            },
                            ticks: {
                                beginAtZero: true,
                                stepSize: 1,
                                callback: (value) => Number(value ?? 0).toLocaleString(),
                                color: getTextColor(),
                                display: true,
                            },
                            grid: {
                                color: getGridColor(),
                                drawBorder: false,
                                lineWidth: 1,
                            },
                            suggestedMin: 0,
                            suggestedMax: null,
                        },
                        x: {
                            ticks: {
                                color: getTextColor(),
                            },
                            grid: {
                                display: false,
                            },
                        },
                    },
                };
            })()
        JS);
    }

    /**
     * @return array<int, string>
     */
    protected function generateColors(int $count): array
    {
        $baseColors = [
            '#60a5fa',
            '#f472b6',
            '#34d399',
            '#fbbf24',
            '#c084fc',
            '#f97316',
            '#38bdf8',
            '#facc15',
            '#14b8a6',
            '#a855f7',
            '#fb7185',
            '#0ea5e9',
        ];

        $colors = [];

        if ($count <= 0) {
            return $colors;
        }

        $baseCount = count($baseColors);

        for ($i = 0; $i < $count; $i++) {
            $colors[] = $baseColors[$i % $baseCount];
        }

        return $colors;
    }
}

