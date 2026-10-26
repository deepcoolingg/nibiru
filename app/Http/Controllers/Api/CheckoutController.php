<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\ActivityLog;
use App\Models\Cart;
use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function options(): JsonResponse
    {
        $countries = collect(CheckoutRequest::ALLOWED_COUNTRIES)
            ->map(fn (string $country): array => [
                'country' => $country,
                'shipping_courier' => $country === 'Indonesia' ? 'J&T Express' : 'DHL Express',
                'payment_gateway' => 'xendit',
                'currencies' => CheckoutRequest::COUNTRY_CURRENCY_MAP[$country] ?? ['IDR'],
            ])
            ->values()
            ->all();

        return response()->json([
            'countries' => $countries,
            'notes' => [
                'shipping' => 'Indonesia uses J&T Express, other supported countries use DHL Express.',
                'payment' => 'Payment is prepared for Xendit invoice flow.',
            ],
        ]);
    }

    public function store(CheckoutRequest $request): OrderResource
    {
        $user = $request->user();

        $cart = Cart::query()->where('user_id', $user->id)->with('items.product')->first();

        abort_unless($cart && $cart->items->isNotEmpty(), 422, 'Cart is empty.');

        // Validate stock availability
        foreach ($cart->items as $item) {
            abort_unless(
                $item->product && $item->product->is_active && $item->product->stock >= $item->qty,
                422,
                "Insufficient stock for {$item->product?->name}."
            );
        }

        $validated = $request->validated();

        // Resolve exchange rate
        $exchangeRate = 1;
        if ($validated['currency'] !== 'IDR') {
            $rate = ExchangeRate::query()->where('currency_code', $validated['currency'])->first();
            abort_unless($rate, 422, "Exchange rate not found for {$validated['currency']}.");
            $exchangeRate = $rate->rate_to_idr;
        }

        // Resolve voucher
        $voucher = null;
        if (! empty($validated['voucher_code'])) {
            $voucher = Voucher::query()
                ->where('code', $validated['voucher_code'])
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses');
                })
                ->first();

            abort_unless($voucher, 422, 'Voucher is invalid or expired.');

            $totalQty = $cart->items->sum('qty');
            if ($voucher->min_qty && $totalQty < $voucher->min_qty) {
                abort(422, "Minimum {$voucher->min_qty} items required for this voucher.");
            }
        }

        $order = DB::transaction(function () use ($cart, $validated, $user, $exchangeRate, $voucher) {
            // Lock products to prevent stock race condition
            foreach ($cart->items as $item) {
                $product = $item->product->refresh();
                abort_unless(
                    $product->is_active && $product->stock >= $item->qty,
                    422,
                    "Insufficient stock for {$product->name}."
                );
            }

            // Lock voucher to prevent over-use race condition
            if ($voucher) {
                $voucher = Voucher::query()->lockForUpdate()->find($voucher->id);
                abort_unless(
                    $voucher && $voucher->is_active && (! $voucher->max_uses || $voucher->used_count < $voucher->max_uses),
                    422,
                    'Voucher is no longer available.'
                );
            }

            $country = $validated['country'];
            $isIndonesia = $country === 'Indonesia';

            $order = Order::query()->create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $user?->id,
                'voucher_id' => $voucher?->id,
                'recipient_name' => $validated['recipient_name'],
                'phone_number' => $validated['phone_number'],
                'shipping_address' => $validated['shipping_address'],
                'country' => $country,
                'order_type' => $isIndonesia ? 'domestic' : 'international',
                'shipping_courier' => $isIndonesia ? 'J&T Express' : 'DHL Express',
                'currency' => $validated['currency'],
                'exchange_rate' => $exchangeRate,
                'shipping_cost' => $validated['shipping_cost'],
                'status' => 'pending',
            ]);

            foreach ($cart->items as $item) {
                $order->orderItems()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price_at_checkout' => $item->product->price,
                    'cost_price_at_checkout' => $item->product->cost_price,
                    'qty' => $item->qty,
                ]);
            }

            // Trigger observer to calculate totals
            $order->save();

            if ($voucher) {
                $voucher->increment('used_count');
            }

            // Clear the cart
            $cart->items()->delete();
            $cart->delete();

            return $order;
        });

        $order->load('orderItems.product', 'voucher');

        ActivityLog::log('order_created', "Order {$order->order_number} created.", $user?->id, [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'grand_total' => $order->grand_total,
        ]);

        return new OrderResource($order);
    }
}
