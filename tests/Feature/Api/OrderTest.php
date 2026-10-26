<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

it('can list authenticated user orders', function () {
    $user = User::factory()->create();
    Order::factory()->count(3)->create(['user_id' => $user->id]);
    Order::factory()->count(2)->create(); // other user's orders

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/orders')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('can paginate orders', function () {
    $user = User::factory()->create();
    Order::factory()->count(15)->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/orders?per_page=5')
        ->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.total', 15);
});

it('can show order detail', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);
    $product = Product::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price_at_checkout' => $product->price,
        'cost_price_at_checkout' => $product->cost_price,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/orders/{$order->id}")
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'id', 'order_number', 'recipient_name', 'grand_total', 'status', 'items',
            ],
        ])
        ->assertJsonCount(1, 'data.items');
});

it('cannot view another users order', function () {
    $user = User::factory()->create();
    $otherOrder = Order::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/orders/{$otherOrder->id}")
        ->assertForbidden();
});

it('requires authentication for orders', function () {
    $this->getJson('/api/orders')
        ->assertUnauthorized();
});
