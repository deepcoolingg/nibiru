<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderItem;
use App\Models\Product;

beforeEach(function () {
    config(['services.xendit.webhook_token' => 'test-webhook-token']);
});

it('processes paid invoice webhook', function () {
    $order = Order::factory()->create(['status' => 'pending']);
    $product = Product::factory()->create(['stock' => 10]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price_at_checkout' => 100000,
        'cost_price_at_checkout' => 40000,
    ]);

    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'gateway' => 'xendit',
        'gateway_transaction_id' => 'inv_paid',
        'status' => 'pending',
    ]);

    $this->postJson('/api/webhooks/xendit', [
        'id' => 'inv_paid',
        'external_id' => $order->order_number,
        'status' => 'PAID',
    ], ['x-callback-token' => 'test-webhook-token'])
        ->assertSuccessful()
        ->assertJsonPath('message', 'Webhook processed.');

    $payment->refresh();
    $order->refresh();
    expect($payment->status)->toBe('completed')
        ->and($payment->paid_at)->not->toBeNull()
        ->and($order->status)->toBe('paid')
        ->and($product->fresh()->stock)->toBe(8);
});

it('does not double-decrement stock on duplicate paid webhook', function () {
    $order = Order::factory()->create(['status' => 'pending']);
    $product = Product::factory()->create(['stock' => 10]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'qty' => 2,
        'price_at_checkout' => 100000,
        'cost_price_at_checkout' => 40000,
    ]);

    Payment::factory()->create([
        'order_id' => $order->id,
        'gateway' => 'xendit',
        'gateway_transaction_id' => 'inv_paid_duplicate',
        'status' => 'pending',
    ]);

    $payload = [
        'id' => 'inv_paid_duplicate',
        'external_id' => $order->order_number,
        'status' => 'PAID',
    ];

    $this->postJson('/api/webhooks/xendit', $payload, ['x-callback-token' => 'test-webhook-token'])
        ->assertSuccessful();

    $this->postJson('/api/webhooks/xendit', $payload, ['x-callback-token' => 'test-webhook-token'])
        ->assertSuccessful();

    expect($product->fresh()->stock)->toBe(8);
});

it('processes expired invoice webhook', function () {
    $order = Order::factory()->create(['status' => 'pending']);
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'gateway' => 'xendit',
        'gateway_transaction_id' => 'inv_expired',
        'status' => 'pending',
    ]);

    $this->postJson('/api/webhooks/xendit', [
        'id' => 'inv_expired',
        'external_id' => $order->order_number,
        'status' => 'EXPIRED',
    ], ['x-callback-token' => 'test-webhook-token'])
        ->assertSuccessful();

    $payment->refresh();
    $order->refresh();
    expect($payment->status)->toBe('expired')
        ->and($payment->paid_at)->toBeNull()
        ->and($order->status)->toBe('pending');
});

it('rejects webhook with invalid token', function () {
    $this->postJson('/api/webhooks/xendit', [
        'id' => 'inv_123',
        'external_id' => 'ORD-123',
        'status' => 'PAID',
    ], ['x-callback-token' => 'wrong-token'])
        ->assertForbidden();
});

it('returns 404 for unknown order', function () {
    $this->postJson('/api/webhooks/xendit', [
        'id' => 'inv_123',
        'external_id' => 'ORD-NONEXISTENT',
        'status' => 'PAID',
    ], ['x-callback-token' => 'test-webhook-token'])
        ->assertNotFound();
});

it('returns 404 for unknown payment', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    $this->postJson('/api/webhooks/xendit', [
        'id' => 'inv_unknown',
        'external_id' => $order->order_number,
        'status' => 'PAID',
    ], ['x-callback-token' => 'test-webhook-token'])
        ->assertNotFound();
});
