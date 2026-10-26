<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'payment_method',
        'gateway',
        'gateway_transaction_id',
        'amount',
        'status',
        'paid_at',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the order that this payment belongs to.
     */
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}