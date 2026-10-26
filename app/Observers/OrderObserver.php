<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderObserver
{
    /**
     * Handle the Order "saving" event - calculate totals automatically.
     */
    public function saving(Order $order): void
    {
        // Calculate subtotal from order items
        $subtotal = $order->orderItems()->sum(DB::raw('qty * price_at_checkout'));
        $order->subtotal = $subtotal;

        // Jika pakai voucher, qty discount tidak berlaku
        $totalQty = $order->orderItems()->sum('qty');
        $hasVoucher = (bool) $order->voucher_id;

        $qtyDiscountAmount = 0;
        $voucherDiscountAmount = 0;

        if ($hasVoucher) {
            $voucher = $order->voucher;
            if ($voucher && $voucher->discount_type === 'percentage') {
                $voucherDiscountAmount = $subtotal * ($voucher->discount_value / 100);
            } elseif ($voucher && $voucher->discount_type === 'fixed') {
                $voucherDiscountAmount = min($voucher->discount_value, $subtotal);
            }
        } else {
            $qtyDiscountAmount = ($totalQty >= 2) ? ($subtotal * 0.05) : 0;
        }

        $order->qty_discount_amount = $qtyDiscountAmount;
        $order->voucher_discount_amount = $voucherDiscountAmount;

        // Calculate total cost price from order items
        $totalCostPrice = $order->orderItems()->sum(DB::raw('qty * cost_price_at_checkout'));
        $order->total_cost_price = $totalCostPrice;

        // Calculate total profit
        $totalProfit = $subtotal - $totalCostPrice - $qtyDiscountAmount - $voucherDiscountAmount;
        $order->total_profit = $totalProfit;

        // Calculate grand total
        $shippingCost = $order->shipping_cost ?? 0;
        $grandTotal = $subtotal - $qtyDiscountAmount - $voucherDiscountAmount + $shippingCost;
        $order->grand_total = $grandTotal;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
