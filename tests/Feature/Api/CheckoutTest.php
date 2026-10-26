<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;

it('can checkout cart as authenticated user', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
    $product = Product::factory()->create(['price' => 100000, 'cost_price' => 40000, 'stock' => 10]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => 2]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/checkout', [
            'recipient_name' => 'John Doe',
            'phone_number' => '08123456789',
            'shipping_address' => 'Jl. Test No. 1',
            'country' => 'Indonesia',
            'currency' => 'IDR',
            'shipping_cost' => 15000,
        ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => ['id', 'order_number', 'recipient_name', 'grand_total', 'status', 'items'],
        ])
        ->assertJsonPath('data.status', 'pending');

    // Cart should be cleared
    $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    $this->assertDatabaseCount('cart_items', 0);

    // Stock should remain unchanged until payment is completed.
    expect($product->fresh()->stock)->toBe(10);
});

it('cannot checkout with empty cart', function () {
    $user = User::factory()->create();
    Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/checkout', [
            'recipient_name' => 'John Doe',
            'phone_number' => '08123456789',
            'shipping_address' => 'Jl. Test No. 1',
            'country' => 'Indonesia',
            'currency' => 'IDR',
            'shipping_cost' => 15000,
        ])->assertStatus(422);
});

it('cannot checkout with insufficient stock', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
    $product = Product::factory()->create(['stock' => 1]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => 5]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/checkout', [
            'recipient_name' => 'John',
            'phone_number' => '08123456789',
            'shipping_address' => 'Address',
            'country' => 'Indonesia',
            'currency' => 'IDR',
            'shipping_cost' => 10000,
        ])->assertStatus(422);
});

it('can checkout with voucher', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
    $product = Product::factory()->create(['price' => 200000, 'cost_price' => 80000, 'stock' => 10]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => 1]);
    $voucher = Voucher::factory()->create(['code' => 'DISKON10', 'discount_value' => 10, 'min_qty' => 1]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/checkout', [
            'recipient_name' => 'Jane',
            'phone_number' => '08111111111',
            'shipping_address' => 'Address',
            'country' => 'Indonesia',
            'currency' => 'IDR',
            'shipping_cost' => 10000,
            'voucher_code' => 'DISKON10',
        ]);

    $response->assertSuccessful();

    expect($voucher->fresh()->used_count)->toBe(1);
});

it('can checkout with foreign currency', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
    $product = Product::factory()->create(['price' => 100000, 'cost_price' => 40000, 'stock' => 5]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => 1]);
    ExchangeRate::factory()->create(['currency_code' => 'MYR', 'rate_to_idr' => 4000]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/checkout', [
            'recipient_name' => 'Foreigner',
            'phone_number' => '+1234567890',
            'shipping_address' => '123 Street',
            'country' => 'Malaysia',
            'currency' => 'MYR',
            'shipping_cost' => 50000,
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.currency', 'MYR')
        ->assertJsonPath('data.exchange_rate', '4000.00');
});

it('validates checkout request', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/checkout', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'recipient_name', 'phone_number', 'shipping_address',
            'country', 'currency', 'shipping_cost',
        ]);
});

it('requires authentication for checkout', function () {
    $this->postJson('/api/checkout', [])
        ->assertUnauthorized();
});

it('returns checkout options for integration preparation', function () {
    $this->getJson('/api/checkout/options')
        ->assertSuccessful()
        ->assertJsonStructure([
            'countries' => [
                ['country', 'shipping_courier', 'payment_gateway', 'currencies'],
            ],
            'notes' => ['shipping', 'payment'],
        ]);
});

it('validates country-currency compatibility on checkout', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
    $product = Product::factory()->create(['stock' => 10, 'is_active' => true]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => 1]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/checkout', [
            'recipient_name' => 'Invalid Currency',
            'phone_number' => '+6281234567890',
            'shipping_address' => 'Jl. Testing Currency Country',
            'country' => 'Indonesia',
            'currency' => 'SGD',
            'shipping_cost' => 10000,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['currency']);
});
