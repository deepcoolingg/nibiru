<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $exchangeRate = (float) ($this->exchange_rate ?: 1);
        $currency = (string) ($this->currency ?: 'IDR');

        $toCurrency = function ($amount) use ($currency, $exchangeRate): float {
            $baseAmount = (float) $amount;
            if ($currency === 'IDR' || $exchangeRate <= 0) {
                return round($baseAmount, 2);
            }

            return round($baseAmount / $exchangeRate, 2);
        };

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'recipient_name' => $this->recipient_name,
            'phone_number' => $this->phone_number,
            'shipping_address' => $this->shipping_address,
            'country' => $this->country,
            'order_type' => $this->order_type,
            'shipping_courier' => $this->shipping_courier,
            'tracking_number' => $this->tracking_number,
            'currency' => $this->currency,
            'exchange_rate' => $this->exchange_rate,
            'subtotal' => $this->subtotal,
            'qty_discount_amount' => $this->qty_discount_amount,
            'voucher_discount_amount' => $this->voucher_discount_amount,
            'shipping_cost' => $this->shipping_cost,
            'grand_total' => $this->grand_total,
            'display_totals' => [
                'currency' => $currency,
                'subtotal' => $toCurrency($this->subtotal),
                'qty_discount_amount' => $toCurrency($this->qty_discount_amount),
                'voucher_discount_amount' => $toCurrency($this->voucher_discount_amount),
                'shipping_cost' => $toCurrency($this->shipping_cost),
                'grand_total' => $toCurrency($this->grand_total),
            ],
            'status' => $this->status,
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'voucher' => new VoucherResource($this->whenLoaded('voucher')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
