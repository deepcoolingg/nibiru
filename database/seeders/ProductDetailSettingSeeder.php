<?php

namespace Database\Seeders;

use App\Models\ProductDetailSetting;
use Illuminate\Database\Seeder;

class ProductDetailSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultProductDetail = config('catalog.product_detail', []);

        ProductDetailSetting::query()->firstOrCreate([], [
            'description' => $defaultProductDetail['description'] ?? null,
            'description_highlights' => $defaultProductDetail['highlights'] ?? [],
            'size_fit_image' => $defaultProductDetail['size_fit_image'] ?? null,
        ]);
    }
}
