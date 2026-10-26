<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ExchangeRate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'currency_code',
        'rate_to_idr',
    ];

    protected function casts(): array
    {
        return [
            'rate_to_idr' => 'decimal:2',
        ];
    }
}