<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SalesByCountryChart extends ChartWidget
{
    use InteractsWithPageFilters;
    protected ?string $heading = 'Sebaran Negara Tujuan';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $month = (int) ($this->filters['month'] ?? now()->month);
        $year = (int) ($this->filters['year'] ?? now()->year);

        $data = Order::whereNotIn('status', ['pending', 'canceled'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->selectRaw('country, COUNT(*) as total')
            ->groupBy('country')
            ->pluck('total', 'country')
            ->toArray();

        $grandTotal = array_sum($data) ?: 1;

        $labels = [];
        foreach ($data as $country => $total) {
            $persen = round(($total / $grandTotal) * 100, 1);
            $labels[] = "{$country} ({$persen}%)";
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pesanan',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#10b981',
                        '#f59e0b',
                        '#3b82f6',
                        '#8b5cf6',
                        '#ef4444',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }
}