<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\XenditService;

it('can create a payment for a pending order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'grand_total' => 150000,
    ]);

    $mock = Mockery::mock(XenditService::class);
    $mock->shouldReceive('createInvoice')->once()->andReturn([
        'id' => 'inv_123',
        'invoice_url' => 'https://checkout.xendit.co/inv_123',
        'status' => 'PENDING',
    ]);
    $this->app->instance(XenditService::class, $mock);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/orders/{$order->id}/pay")
        ->assertSuccessful()
        ->assertJsonStructure(['payment_id', 'invoice_url', 'status'])
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('invoice_url', 'https://checkout.xendit.co/inv_123');

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'gateway' => 'xendit',
        'gateway_transaction_id' => 'inv_123',
        'status' => 'pending',
    ]);
});

it('creates invoice using order currency amount', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'currency' => 'MYR',
        'exchange_rate' => 4000,
        'shipping_cost' => 80000,
    ]);

    $mock = Mockery::mock(XenditService::class);
    $mock->shouldReceive('createInvoice')
        ->once()
        ->withArgs(function (array $payload): bool {
            return ($payload['currency'] ?? null) === 'MYR'
                && (float) ($payload['amount'] ?? 0) === 20.0;
        })
        ->andReturn([
            'id' => 'inv_myr_001',
            'invoice_url' => 'https://checkout.xendit.co/inv_myr_001',
            'status' => 'PENDING',
        ]);
    $this->app->instance(XenditService::class, $mock);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/orders/{$order->id}/pay")
        ->assertSuccessful()
        ->assertJsonPath('status', 'pending');
});

it('returns existing pending payment instead of creating new one', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'grand_total' => 150000,
    ]);

    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'gateway' => 'xendit',
        'gateway_transaction_id' => 'inv_existing',
        'status' => 'pending',
    ]);

    $mock = Mockery::mock(XenditService::class);
    $mock->shouldReceive('getInvoice')->once()->with('inv_existing')->andReturn([
        'id' => 'inv_existing',
        'invoice_url' => 'https://checkout.xendit.co/inv_existing',
    ]);
    $this->app->instance(XenditService::class, $mock);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/orders/{$order->id}/pay")
        ->assertSuccessful()
        ->assertJsonPath('payment_id', $payment->id);
});

it('cannot pay for a non-pending order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'paid',
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/orders/{$order->id}/pay")
        ->assertStatus(422);
});

it('cannot pay for another users order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['status' => 'pending']);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/orders/{$order->id}/pay")
        ->assertForbidden();
});

it('can check payment status', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'amount' => 150000,
        'status' => 'completed',
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/payments/{$payment->id}")
        ->assertSuccessful()
        ->assertJsonStructure(['payment_id', 'order_id', 'amount', 'status', 'gateway', 'paid_at'])
        ->assertJsonPath('status', 'completed');
});

it('cannot check another users payment', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(); // different user
    $payment = Payment::factory()->create(['order_id' => $order->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/payments/{$payment->id}")
        ->assertForbidden();
});

it('requires authentication for payment endpoints', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    $this->postJson("/api/orders/{$order->id}/pay")->assertUnauthorized();
    $this->getJson('/api/payments/some-id')->assertUnauthorized();
});
