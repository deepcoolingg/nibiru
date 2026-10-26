<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Voucher extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_qty',
        'max_uses',
        'used_count',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the orders that use this voucher.
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }
}