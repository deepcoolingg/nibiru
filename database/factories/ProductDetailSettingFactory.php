<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductDetailSetting>
 */
class ProductDetailSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => 'Bold yet soft in vibe-this lace pashmina is made to feel light, comfy, and super photogenic. Sweet lace frame, airy drape, and cupcake mood that looks cute from every angle.',
            'description_highlights' => [
                'Material: Premium pashmina (soft + airy feel)',
                'Transparency: Semi-opaque (depends on styling)',
                'Production: Made with care in small batches',
            ],
            'size_fit_image' => 'products/size-fit/default-size-fit.jpg',
        ];
    }
}
