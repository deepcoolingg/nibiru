<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CartItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'cart_id',
        'product_id',
        'qty',
    ];

    /**
     * Get the cart that owns this item.
     */
    public function cart(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product for this cart item.
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}