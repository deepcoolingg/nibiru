<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_number',
        'user_id',
        'voucher_id',
        'recipient_name',
        'phone_number',
        'shipping_address',
        'country',
        'order_type',
        'shipping_courier',
        'tracking_number',
        'currency',
        'exchange_rate',
        'subtotal',
        'qty_discount_amount',
        'voucher_discount_amount',
        'shipping_cost',
        'total_cost_price',
        'total_profit',
        'grand_total',
        'status',
    ];

    /**
     * Get the user that placed the order.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'qty_discount_amount' => 'decimal:2',
            'voucher_discount_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_cost_price' => 'decimal:2',
            'total_profit' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    /**
     * Get the voucher applied to the order.
     */
    public function voucher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Get the order items for this order.
     */
    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payments for this order.
     */
    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }
}