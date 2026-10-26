<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected static function booted(): void
    {
        static::updating(function (self $product): void {
            if (! $product->isDirty('stock')) {
                return;
            }

            $previousStock = (int) ($product->getOriginal('stock') ?? 0);
            $nextStock = (int) $product->stock;

            if ($previousStock <= 0 && $nextStock > 0) {
                $product->restocked_at = now();
                return;
            }

            if ($nextStock <= 0) {
                $product->restocked_at = null;
            }
        });
    }

    protected $fillable = [
        'name',
        'description',
        'image',
        'size_fit_image',
        'color_name',
        'color_hex',
        'sku',
        'price',
        'cost_price',
        'stock',
        'restocked_at',
        'weight',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'weight' => 'decimal:2',
            'is_active' => 'boolean',
            'restocked_at' => 'datetime',
        ];
    }
}