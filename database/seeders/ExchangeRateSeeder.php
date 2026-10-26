<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExchangeRate::query()->updateOrCreate(
            ['currency_code' => 'USD'],
            ['rate_to_idr' => 15_850],
        );

        ExchangeRate::query()->updateOrCreate(
            ['currency_code' => 'SGD'],
            ['rate_to_idr' => 11_950],
        );

        ExchangeRate::query()->updateOrCreate(
            ['currency_code' => 'MYR'],
            ['rate_to_idr' => 3_250],
        );

        ExchangeRate::query()->updateOrCreate(
            ['currency_code' => 'THB'],
            ['rate_to_idr' => 425],
        );

        ExchangeRate::query()->updateOrCreate(
            ['currency_code' => 'PHP'],
            ['rate_to_idr' => 275],
        );
    }
}
