<?php

use App\Services\BiteshipService;

it('can get shipping rates', function () {
    $mock = Mockery::mock(BiteshipService::class);
    $mock->shouldReceive('getShippingRates')->once()->andReturn([
        'success' => true,
        'pricing' => [
            [
                'courier_name' => 'JNE',
                'courier_service_name' => 'REG',
                'price' => 15000,
                'duration' => '2-3 days',
            ],
        ],
    ]);
    $this->app->instance(BiteshipService::class, $mock);

    $this->postJson('/api/shipping/rates', [
        'origin_postal_code' => '12345',
        'destination_postal_code' => '67890',
        'couriers' => 'jne',
        'items' => [
            ['name' => 'Test Product', 'quantity' => 1, 'weight' => 500, 'value' => 100000],
        ],
    ])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pricing.0.courier_name', 'JNE');
});

it('validates required fields for shipping rates', function () {
    $this->postJson('/api/shipping/rates', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'origin_postal_code',
            'destination_postal_code',
            'couriers',
            'items',
        ]);
});

it('validates items array structure for shipping rates', function () {
    $this->postJson('/api/shipping/rates', [
        'origin_postal_code' => '12345',
        'destination_postal_code' => '67890',
        'couriers' => 'jne',
        'items' => [
            ['name' => 'Test'],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'items.0.quantity',
            'items.0.weight',
            'items.0.value',
        ]);
});

it('can track a shipment', function () {
    $user = \App\Models\User::factory()->create();

    $mock = Mockery::mock(BiteshipService::class);
    $mock->shouldReceive('trackShipment')->once()->with('bts_order_123')->andReturn([
        'success' => true,
        'id' => 'bts_order_123',
        'status' => 'delivered',
    ]);
    $this->app->instance(BiteshipService::class, $mock);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/shipping/track/bts_order_123')
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('status', 'delivered');
});
