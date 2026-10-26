<?php

use App\Models\ProductDetailSetting;
use App\Models\Product;

it('can list active products', function () {
    Product::factory()->count(3)->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    $this->getJson('/api/products')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('can paginate products', function () {
    Product::factory()->count(15)->create();

    $response = $this->getJson('/api/products?per_page=5');

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.total', 15);
});

it('can search products by name', function () {
    Product::factory()->create(['name' => 'Batik Tulis Cirebon']);
    Product::factory()->create(['name' => 'Songket Palembang']);

    $this->getJson('/api/products?search=Batik')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Batik Tulis Cirebon');
});

it('can show a single product', function () {
    $globalSetting = ProductDetailSetting::factory()->create([
        'description' => 'Global description from admin panel',
        'description_highlights' => [
            'Material: Premium pashmina (soft + airy feel)',
            'Transparency: Semi-opaque (depends on styling)',
            'Production: Made with care in small batches',
        ],
        'size_fit_image' => 'products/size-fit/from-admin.jpg',
    ]);

    $product = Product::factory()->create([
        'is_active' => true,
        'description' => null,
        'size_fit_image' => null,
        'color_name' => 'Blackberry',
        'color_hex' => '#1F2937',
    ]);

    $this->getJson("/api/products/{$product->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.name', $product->name)
        ->assertJsonPath('data.description', $globalSetting->description)
        ->assertJsonPath('data.size_fit_image', $globalSetting->size_fit_image)
        ->assertJsonPath('data.color_name', $product->color_name)
        ->assertJsonPath('data.color_hex', $product->color_hex)
        ->assertJsonPath('data.description_highlights.0', $globalSetting->description_highlights[0])
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'description_highlights',
                'image',
                'size_fit_image',
                'color_name',
                'color_hex',
                'sku',
                'price',
                'stock',
                'weight',
            ],
        ]);
});

it('returns 404 for inactive product', function () {
    $product = Product::factory()->create(['is_active' => false]);

    $this->getJson("/api/products/{$product->id}")
        ->assertNotFound();
});

it('returns 404 for nonexistent product', function () {
    $this->getJson('/api/products/nonexistent-uuid')
        ->assertNotFound();
});
