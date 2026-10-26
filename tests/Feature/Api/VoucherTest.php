<?php

use App\Models\Voucher;

it('can check a valid voucher', function () {
    Voucher::factory()->create(['code' => 'HEMAT10', 'discount_value' => 10]);

    $this->postJson('/api/vouchers/check', ['code' => 'HEMAT10'])
        ->assertSuccessful()
        ->assertJsonPath('data.code', 'HEMAT10')
        ->assertJsonPath('data.discount_value', '10.00');
});

it('returns 422 for invalid voucher code', function () {
    $this->postJson('/api/vouchers/check', ['code' => 'INVALID'])
        ->assertStatus(422);
});

it('returns 422 for expired voucher', function () {
    Voucher::factory()->create([
        'code' => 'EXPIRED',
        'valid_until' => now()->subDay(),
    ]);

    $this->postJson('/api/vouchers/check', ['code' => 'EXPIRED'])
        ->assertStatus(422);
});

it('returns 422 for inactive voucher', function () {
    Voucher::factory()->create([
        'code' => 'INACTIVE',
        'is_active' => false,
    ]);

    $this->postJson('/api/vouchers/check', ['code' => 'INACTIVE'])
        ->assertStatus(422);
});

it('returns 422 for fully used voucher', function () {
    Voucher::factory()->create([
        'code' => 'MAXED',
        'max_uses' => 5,
        'used_count' => 5,
    ]);

    $this->postJson('/api/vouchers/check', ['code' => 'MAXED'])
        ->assertStatus(422);
});

it('validates code is required', function () {
    $this->postJson('/api/vouchers/check', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});
