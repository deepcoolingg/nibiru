<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetailSetting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'description',
        'description_highlights',
        'size_fit_image',
    ];

    protected function casts(): array
    {
        return [
            'description_highlights' => 'array',
        ];
    }
}
