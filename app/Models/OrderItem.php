<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'price_at_checkout',
        'cost_price_at_checkout',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'price_at_checkout' => 'decimal:2',
            'cost_price_at_checkout' => 'decimal:2',
        ];
    }

    /**
     * Get the order that contains this item.
     */
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product for this order item.
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}