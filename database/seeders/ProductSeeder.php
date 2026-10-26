<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Hijab Katun Premium',
            'description' => 'Hijab katun 100% berkualitas premium, lembut dan breathable',
            'image' => 'products/hijab-katun-premium.jpg',
            'size_fit_image' => 'products/size-fit/hijab-katun-premium-size-fit.jpg',
            'color_name' => 'Lemonberry',
            'color_hex' => '#E9D76D',
            'sku' => 'HIJAB-001',
            'price' => 89_000,
            'cost_price' => 35_000,
            'stock' => 50,
            'weight' => 150,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Hijab Satin Glossy',
            'description' => 'Hijab satin glossy dengan kilau mewah, cocok untuk acara formal',
            'image' => 'products/hijab-satin-glossy.jpg',
            'size_fit_image' => 'products/size-fit/hijab-satin-glossy-size-fit.jpg',
            'color_name' => 'Strawberry',
            'color_hex' => '#F3B4CC',
            'sku' => 'HIJAB-002',
            'price' => 145_000,
            'cost_price' => 58_000,
            'stock' => 35,
            'weight' => 120,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Hijab Jersey Stretch',
            'description' => 'Hijab jersey stretch yang nyaman dan fleksibel, cocok sehari-hari',
            'image' => 'products/hijab-jersey-stretch.jpg',
            'size_fit_image' => 'products/size-fit/hijab-jersey-stretch-size-fit.jpg',
            'color_name' => 'Blueberry',
            'color_hex' => '#77BDF7',
            'sku' => 'HIJAB-003',
            'price' => 65_000,
            'cost_price' => 26_000,
            'stock' => 100,
            'weight' => 100,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Hijab Chiffon Pashmina',
            'description' => 'Hijab chiffon pashmina ringan dan adem, sempurna untuk iklim tropis',
            'image' => 'products/hijab-chiffon-pashmina.jpg',
            'size_fit_image' => 'products/size-fit/hijab-chiffon-pashmina-size-fit.jpg',
            'color_name' => 'Lemonberry',
            'color_hex' => '#E9D76D',
            'sku' => 'HIJAB-004',
            'price' => 125_000,
            'cost_price' => 50_000,
            'stock' => 45,
            'weight' => 110,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Hijab Voal Motif',
            'description' => 'Hijab voal dengan motif prints cantik, berbagai pilihan warna',
            'image' => 'products/hijab-voal-motif.jpg',
            'size_fit_image' => 'products/size-fit/hijab-voal-motif-size-fit.jpg',
            'color_name' => 'Cloudberry',
            'color_hex' => '#E8E1D7',
            'sku' => 'HIJAB-005',
            'price' => 95_000,
            'cost_price' => 38_000,
            'stock' => 60,
            'weight' => 135,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Hijab Wool Musim Dingin',
            'description' => 'Hijab wool premium untuk musim dingin, hangat dan elegan',
            'image' => 'products/hijab-wool-musim-dingin.jpg',
            'size_fit_image' => 'products/size-fit/hijab-wool-musim-dingin-size-fit.jpg',
            'color_name' => 'Blackberry',
            'color_hex' => '#1F2A44',
            'sku' => 'HIJAB-006',
            'price' => 175_000,
            'cost_price' => 70_000,
            'stock' => 30,
            'weight' => 200,
            'is_active' => true,
        ]);
    }
}
