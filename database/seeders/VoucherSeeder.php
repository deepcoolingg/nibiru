<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Voucher::create([
            'code' => 'WELCOME10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_qty' => 1,
            'max_uses' => 100,
            'used_count' => 0,
            'valid_until' => now()->addMonths(6),
            'is_active' => true,
        ]);

        Voucher::create([
            'code' => 'NBRU10PROMO',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_qty' => 2,
            'max_uses' => 150,
            'used_count' => 0,
            'valid_until' => now()->addMonths(3),
            'is_active' => true,
        ]);

        Voucher::create([
            'code' => 'MEMBER10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_qty' => 1,
            'max_uses' => 200,
            'used_count' => 0,
            'valid_until' => now()->addMonths(12),
            'is_active' => true,
        ]);
    }
}
