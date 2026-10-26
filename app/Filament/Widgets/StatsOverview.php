<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    // Biar deretan kotak ini nongkrong paling atas di dashboard
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $validStatuses = ['paid', 'shipped', 'completed'];

        $month = (int) ($this->filters['month'] ?? now()->month);
        $year = (int) ($this->filters['year'] ?? now()->year);

        // --- 1. AMBIL DATA BULAN YANG DIPILIH ---
        $salesBulanIni = Order::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->whereIn('status', $validStatuses)
            ->selectRaw('COALESCE(SUM(subtotal - qty_discount_amount - voucher_discount_amount), 0) as total')
            ->value('total');

        $profitBulanIni = Order::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->whereIn('status', $validStatuses)->sum('total_profit');

        $ordersBulanIni = Order::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->whereIn('status', $validStatuses)->count();

        // --- 2. AMBIL DATA BULAN SEBELUMNYA ---
        $bulanLalu = Carbon::create($year, $month)->subMonth();
        $salesBulanLalu = Order::whereMonth('created_at', $bulanLalu->month)
            ->whereYear('created_at', $bulanLalu->year)
            ->whereIn('status', $validStatuses)
            ->selectRaw('COALESCE(SUM(subtotal - qty_discount_amount - voucher_discount_amount), 0) as total')
            ->value('total');

        $profitBulanLalu = Order::whereMonth('created_at', $bulanLalu->month)
            ->whereYear('created_at', $bulanLalu->year)
            ->whereIn('status', $validStatuses)->sum('total_profit');

        $ordersBulanLalu = Order::whereMonth('created_at', $bulanLalu->month)
            ->whereYear('created_at', $bulanLalu->year)
            ->whereIn('status', $validStatuses)->count();

        // --- 3. RUMUS PERSENTASE NAIK/TURUN ---
        $hitungPersen = function ($sekarang, $lalu) {
            if ($lalu == 0) return $sekarang > 0 ? 100 : 0; // Jaga-jaga kalau bulan lalu 0
            return (($sekarang - $lalu) / $lalu) * 100;
        };

        $persenSales = $hitungPersen($salesBulanIni, $salesBulanLalu);
        $persenProfit = $hitungPersen($profitBulanIni, $profitBulanLalu);
        $persenOrders = $hitungPersen($ordersBulanIni, $ordersBulanLalu);

        // --- 4. RENDER TAMPILAN TEXT & WARNA ---
        // INI BAGIAN YANG DIUBAH BIAR MIRIP GAMBAR LU BRE
        $formatTrend = function ($persen) {
            $tanda = $persen >= 0 ? '+' : ''; // Kasih tanda plus kalau naik
            return $tanda . round($persen, 2) . '% vs last month'; // Format teks persis kayak gambar
        };
        
        // Icon dan warna tetep ngikutin standar Filament
        $iconTrend = fn($persen) => $persen >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $colorTrend = fn($persen) => $persen >= 0 ? 'success' : 'danger';

        return [
            Stat::make('Total Sales', 'Rp ' . number_format($salesBulanIni, 0, ',', '.'))
                ->description($formatTrend($persenSales))
                ->descriptionIcon($iconTrend($persenSales))
                ->color($colorTrend($persenSales)),

            Stat::make('Margin Profit', 'Rp ' . number_format($profitBulanIni, 0, ',', '.'))
                ->description($formatTrend($persenProfit))
                ->descriptionIcon($iconTrend($persenProfit))
                ->color($colorTrend($persenProfit)),

            Stat::make('Total Orders', $ordersBulanIni)
                ->description($formatTrend($persenOrders))
                ->descriptionIcon($iconTrend($persenOrders))
                ->color($colorTrend($persenOrders)),
        ];
    }
}