<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class PopularProductsWidget extends BaseWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = [
        'md' => 4,
        'xl' => 4,
    ];

    protected function getTableHeading(): string
    {
        $month = (int) ($this->filters['month'] ?? now()->month);
        $year = (int) ($this->filters['year'] ?? now()->year);

        return 'Top Selling Products — ' . \Carbon\Carbon::create($year, $month)->translatedFormat('F Y');
    }

    public function table(Table $table): Table
    {
        $month = (int) ($this->filters['month'] ?? now()->month);
        $year = (int) ($this->filters['year'] ?? now()->year);

        return $table
            ->query(
                Product::query()
                    ->select('products.*')
                    ->addSelect([
                        'total_earnings' => OrderItem::query()
                            ->selectRaw('COALESCE(SUM(order_items.qty * order_items.price_at_checkout), 0)')
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->whereColumn('order_items.product_id', 'products.id')
                            ->whereIn('orders.status', ['paid', 'shipped', 'completed'])
                            ->whereMonth('orders.created_at', $month)
                            ->whereYear('orders.created_at', $year),
                    ])
                    ->orderByDesc('total_earnings')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('')
                    ->size(40)
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('total_earnings')
                    ->label('Earnings')
                    ->money('IDR')
                    ->color('success'),
            ])
            ->paginated(false);
    }
}