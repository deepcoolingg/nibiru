<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

it('can view cart as guest with session id', function () {
    $sessionId = fake()->uuid();
    $cart = Cart::factory()->create(['session_id' => $sessionId, 'user_id' => null]);
    $product = Product::factory()->create();
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => 2]);

    $this->getJson('/api/cart', ['X-Session-Id' => $sessionId])
        ->assertSuccessful()
        ->assertJsonPath('data.id', $cart->id)
        ->assertJsonCount(1, 'data.items');
});

it('can view cart as authenticated user', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
    $product = Product::factory()->create();
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/cart')
        ->assertSuccessful()
        ->assertJsonPath('data.id', $cart->id);
});

it('returns 400 without session id or auth', function () {
    $this->getJson('/api/cart')
        ->assertStatus(400);
});

it('can add item to cart', function () {
    $sessionId = fake()->uuid();
    $product = Product::factory()->create(['is_active' => true, 'stock' => 10]);

    $this->postJson('/api/cart/items', [
        'product_id' => $product->id,
        'qty' => 2,
    ], ['X-Session-Id' => $sessionId])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.qty', 2);
});

it('increments qty when adding existing product to cart', function () {
    $sessionId = fake()->uuid();
    $cart = Cart::factory()->create(['session_id' => $sessionId, 'user_id' => null]);
    $product = Product::factory()->create(['is_active' => true]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => 1]);

    $this->postJson('/api/cart/items', [
        'product_id' => $product->id,
        'qty' => 3,
    ], ['X-Session-Id' => $sessionId])
        ->assertSuccessful()
        ->assertJsonPath('data.items.0.qty', 4);
});

it('can update cart item qty', function () {
    $sessionId = fake()->uuid();
    $cart = Cart::factory()->create(['session_id' => $sessionId, 'user_id' => null]);
    $product = Product::factory()->create();
    $item = CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => 1]);

    $this->putJson("/api/cart/items/{$item->id}", [
        'qty' => 5,
    ], ['X-Session-Id' => $sessionId])
        ->assertSuccessful()
        ->assertJsonPath('data.items.0.qty', 5);
});

it('cannot update another carts item', function () {
    $sessionId = fake()->uuid();
    Cart::factory()->create(['session_id' => $sessionId, 'user_id' => null]);
    $otherCart = Cart::factory()->create(['session_id' => fake()->uuid(), 'user_id' => null]);
    $item = CartItem::factory()->create(['cart_id' => $otherCart->id]);

    $this->putJson("/api/cart/items/{$item->id}", [
        'qty' => 5,
    ], ['X-Session-Id' => $sessionId])
        ->assertForbidden();
});

it('can remove cart item', function () {
    $sessionId = fake()->uuid();
    $cart = Cart::factory()->create(['session_id' => $sessionId, 'user_id' => null]);
    $product = Product::factory()->create();
    $item = CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id]);

    $this->deleteJson("/api/cart/items/{$item->id}", [], ['X-Session-Id' => $sessionId])
        ->assertSuccessful()
        ->assertJsonCount(0, 'data.items');

    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

it('validates add item request', function () {
    $sessionId = fake()->uuid();

    $this->postJson('/api/cart/items', [
        'product_id' => '',
        'qty' => 0,
    ], ['X-Session-Id' => $sessionId])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id', 'qty']);
});
