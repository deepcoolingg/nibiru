<?php

use App\Models\ExchangeRate;

it('can list all exchange rates', function () {
    ExchangeRate::factory()->create(['currency_code' => 'USD', 'rate_to_idr' => 15500]);
    ExchangeRate::factory()->create(['currency_code' => 'MYR', 'rate_to_idr' => 3400]);

    $this->getJson('/api/exchange-rates')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'currency_code', 'rate_to_idr', 'updated_at'],
            ],
        ]);
});

it('returns empty array when no rates exist', function () {
    $this->getJson('/api/exchange-rates')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});
