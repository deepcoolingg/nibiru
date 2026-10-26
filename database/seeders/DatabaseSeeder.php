<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users (admin + realistic customer dataset)
        $this->call(UserSeeder::class);

        // Seed exchange rates
        $this->call(ExchangeRateSeeder::class);

        // Seed global product detail settings
        $this->call(ProductDetailSettingSeeder::class);

        // Seed products
        $this->call(ProductSeeder::class);

        // Seed vouchers
        $this->call(VoucherSeeder::class);

        // Seed orders with dummy data
        $this->call(OrderSeeder::class);
    }
}
