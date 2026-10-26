<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\HtmlString;

class SalesTargetWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    protected ?string $heading = 'Monthly Target';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected ?string $maxHeight = '200px';

    protected string $view = 'filament.widgets.sales-target';

    public function getTargetData(): array
    {
        $month = (int) ($this->filters['month'] ?? now()->month);
        $year = (int) ($this->filters['year'] ?? now()->year);

        // Hitung total qty item terjual
        $terjual = (int) Order::whereMonth('orders.created_at', $month)
            ->whereYear('orders.created_at', $year)
            ->whereIn('orders.status', ['paid', 'shipped', 'completed'])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.qty');

        $target = 50;
        $persen = $target > 0 ? round(($terjual / $target) * 100, 1) : 0;
        $sisa = max($target - $terjual, 0);

        if ($terjual >= $target) {
            $narasi = "Target Achieved! Terjual <strong>{$terjual}</strong> item bulan ini.";
        } else {
            $narasi = "Keep it up! Kurang <strong>{$sisa}</strong> item lagi dari target <strong>{$target}</strong>.";
        }

        return [
            'terjual' => $terjual,
            'target' => $target,
            'persen' => $persen,
            'sisa' => $sisa,
            'narasi' => $narasi,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'circumference' => 180,
            'rotation' => 270,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => ['enabled' => true],
            ],
            'cutout' => '75%',
        ];
    }

    protected function getData(): array
    {
        $data = $this->getTargetData();

        return [
            'datasets' => [
                [
                    'data' => [$data['terjual'], $data['sisa']],
                    'backgroundColor' => ['#bcd5fb', '#374151'],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['Achieved', 'Remaining'],
        ];
    }
}