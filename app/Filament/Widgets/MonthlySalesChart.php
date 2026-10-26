<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class MonthlySalesChart extends ChartWidget
{
    use InteractsWithPageFilters;
    protected ?string $heading = 'Grafik Pendapatan Bulanan';
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = [
        'md' => 4,
        'xl' => 4,
    ];

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
        ];
    }

    protected function getData(): array
    {
        $activeYear = (int) ($this->filters['year'] ?? date('Y'));

        $orders = Order::selectRaw('EXTRACT(MONTH FROM created_at) as month, SUM(subtotal - qty_discount_amount - voucher_discount_amount) as total')
            ->whereYear('created_at', $activeYear)
            ->whereIn('status', ['paid', 'shipped', 'completed'])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $orders[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pendapatan (Rp)',
                    'data' => $data,
                    'borderColor' => '#bcd5fb',
                    'backgroundColor' => 'rgba(188, 213, 251, 0.25)',
                    'fill' => 'start',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
        ];
    }
}