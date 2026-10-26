<?php

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\XenditService;

it('logs activity on user registration', function () {
    $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'log@test.com',
        'phone_number' => '+6281234567000',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'birthday' => [
            'day' => 10,
            'month' => 'January',
            'year' => 2000,
        ],
    ])->assertCreated();

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'user_registered',
    ]);
});

it('logs activity on user login', function () {
    $user = User::factory()->create(['password' => 'secret123']);

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ])->assertSuccessful();

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'user_login',
        'user_id' => $user->id,
    ]);
});

it('logs activity on user logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/logout')
        ->assertSuccessful();

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'user_logout',
        'user_id' => $user->id,
    ]);
});

it('logs activity on order creation via checkout', function () {
    $user = User::factory()->create();
    $product = \App\Models\Product::factory()->create(['stock' => 10, 'is_active' => true]);

    $cart = \App\Models\Cart::factory()->create(['user_id' => $user->id]);
    \App\Models\CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 1,
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/checkout', [
            'recipient_name' => 'Tester',
            'phone_number' => '08123456789',
            'shipping_address' => 'Jl. Test',
            'country' => 'Indonesia',
            'order_type' => 'domestic',
            'currency' => 'IDR',
            'shipping_cost' => 15000,
        ])->assertSuccessful();

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'order_created',
        'user_id' => $user->id,
    ]);

    $log = ActivityLog::query()->where('action', 'order_created')->first();
    expect($log->metadata)->toHaveKeys(['order_id', 'order_number', 'grand_total']);
});

it('logs activity on payment creation', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'grand_total' => 100000,
    ]);

    $mock = Mockery::mock(XenditService::class);
    $mock->shouldReceive('createInvoice')->once()->andReturn([
        'id' => 'inv_log_test',
        'invoice_url' => 'https://checkout.xendit.co/inv_log_test',
        'status' => 'PENDING',
    ]);
    $this->app->instance(XenditService::class, $mock);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/orders/{$order->id}/pay")
        ->assertSuccessful();

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'payment_created',
        'user_id' => $user->id,
    ]);

    $log = ActivityLog::query()->where('action', 'payment_created')->first();
    expect($log->metadata)->toHaveKeys(['order_id', 'payment_id', 'amount']);
});

it('logs activity on webhook payment completed', function () {
    config(['services.xendit.webhook_token' => 'test-token']);

    $order = Order::factory()->create(['status' => 'pending']);
    Payment::factory()->create([
        'order_id' => $order->id,
        'gateway' => 'xendit',
        'gateway_transaction_id' => 'inv_webhook_log',
        'status' => 'pending',
    ]);

    $this->postJson('/api/webhooks/xendit', [
        'id' => 'inv_webhook_log',
        'external_id' => $order->order_number,
        'status' => 'PAID',
    ], ['x-callback-token' => 'test-token'])
        ->assertSuccessful();

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'payment_completed',
        'user_id' => $order->user_id,
    ]);

    $log = ActivityLog::query()->where('action', 'payment_completed')->first();
    expect($log->metadata)->toHaveKeys(['order_id', 'payment_id', 'xendit_invoice_id']);
});

it('can create activity log using static log method', function () {
    $user = User::factory()->create();

    $log = ActivityLog::log('test_action', 'Test description.', $user->id, ['key' => 'value']);

    expect($log)->toBeInstanceOf(ActivityLog::class)
        ->and($log->action)->toBe('test_action')
        ->and($log->description)->toBe('Test description.')
        ->and($log->user_id)->toBe($user->id)
        ->and($log->metadata)->toBe(['key' => 'value']);
});
