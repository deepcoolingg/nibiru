<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data transaksi lama
        Payment::query()->delete();
        OrderItem::query()->delete();
        Order::query()->delete();

        $products = Product::query()->where('is_active', true)->get();

        if ($products->isEmpty()) {
            $this->command->error('Belum ada produk! Tambahkan produk dulu.');

            return;
        }

        $customers = User::query()->where('role', 'customer')->get();
        if ($customers->count() < 20) {
            $newCustomers = User::factory()->count(20 - $customers->count())->create([
                'role' => 'customer',
                'is_active' => true,
            ]);
            $customers = $customers->merge($newCustomers);
        }

        $customers->each(function (User $customer): void {
            if (blank($customer->phone_number)) {
                $customer->forceFill([
                    'phone_number' => fake()->numerify('08##########'),
                ])->saveQuietly();
            }
        });

        $countryProfiles = [
            'Indonesia' => ['currency' => 'IDR', 'rate' => 1.00, 'courier' => 'J&T Express', 'shipping' => [12000, 30000]],
            'Malaysia' => ['currency' => 'MYR', 'rate' => 4000.00, 'courier' => 'DHL Express', 'shipping' => [90000, 180000]],
            'Singapore' => ['currency' => 'SGD', 'rate' => 12000.00, 'courier' => 'DHL Express', 'shipping' => [95000, 175000]],
            'Thailand' => ['currency' => 'THB', 'rate' => 450.00, 'courier' => 'DHL Express', 'shipping' => [90000, 170000]],
            'Philippines' => ['currency' => 'PHP', 'rate' => 280.00, 'courier' => 'DHL Express', 'shipping' => [100000, 185000]],
        ];

        $paymentMethods = ['bank_transfer', 'e_wallet', 'credit_card', 'qris', 'virtual_account'];
        $statusWeights = ['pending', 'paid', 'shipped', 'completed', 'canceled', 'completed', 'shipped', 'completed'];
        $ordersPerYear = 40;
        $targetYears = range(2026, 2030);

        foreach ($targetYears as $year) {
            for ($i = 1; $i <= $ordersPerYear; $i++) {
                $customer = $customers->random();
                $country = fake()->randomElement(array_keys($countryProfiles));
                $profile = $countryProfiles[$country];
                $status = fake()->randomElement($statusWeights);
                $isInternational = $country !== 'Indonesia';

                $createdAt = Carbon::create($year, fake()->numberBetween(1, 12), fake()->numberBetween(1, 28), fake()->numberBetween(8, 21), fake()->numberBetween(0, 59), fake()->numberBetween(0, 59));

                $order = new Order([
                    'order_number' => 'ORD-' . $year . strtoupper(Str::random(6)),
                    'user_id' => $customer->id,
                    'recipient_name' => $customer->name,
                    'phone_number' => $customer->phone_number,
                    'shipping_address' => fake()->streetAddress() . ', ' . fake()->city() . ', ' . $country,
                    'country' => $country,
                    'order_type' => $isInternational ? 'international' : 'domestic',
                    'shipping_courier' => $profile['courier'],
                    'tracking_number' => in_array($status, ['shipped', 'completed'], true) ? strtoupper(fake()->bothify('TRK##########')) : null,
                    'currency' => $profile['currency'],
                    'exchange_rate' => $profile['rate'],
                    'subtotal' => 0,
                    'shipping_cost' => fake()->numberBetween($profile['shipping'][0], $profile['shipping'][1]),
                    'qty_discount_amount' => 0,
                    'voucher_discount_amount' => 0,
                    'total_cost_price' => 0,
                    'total_profit' => 0,
                    'grand_total' => 0,
                    'status' => $status,
                ]);

                $order->created_at = $createdAt;
                $order->updated_at = $createdAt;
                $order->saveQuietly();

                $pickedProducts = $products->shuffle()->take(fake()->numberBetween(1, min(4, $products->count())));
                foreach ($pickedProducts as $product) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price_at_checkout' => $product->price,
                        'cost_price_at_checkout' => $product->cost_price,
                        'qty' => fake()->numberBetween(1, 3),
                    ]);
                }

                $order->created_at = $createdAt;
                $order->updated_at = $createdAt;
                $order->save();

                $shouldCreatePendingPayment = $status === 'pending' && fake()->boolean(70);
                $shouldCreateFinalPayment = in_array($status, ['paid', 'shipped', 'completed', 'canceled'], true);

                if (! $shouldCreatePendingPayment && ! $shouldCreateFinalPayment) {
                    continue;
                }

                $isCanceled = $status === 'canceled';
                $paymentStatus = $isCanceled ? 'failed' : ($status === 'pending' ? 'pending' : 'completed');
                $paidAt = $paymentStatus === 'completed' ? $createdAt->copy()->addHours(fake()->numberBetween(1, 24)) : null;

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => fake()->randomElement($paymentMethods),
                    'gateway' => 'xendit',
                    'gateway_transaction_id' => 'INV-' . strtoupper(Str::random(14)),
                    'amount' => $order->grand_total,
                    'status' => $paymentStatus,
                    'paid_at' => $paidAt,
                    'raw_response' => json_encode([
                        'invoice_id' => 'INV-' . strtoupper(Str::random(10)),
                        'external_id' => $order->order_number,
                        'payment_method' => $paymentStatus === 'pending' ? null : fake()->randomElement($paymentMethods),
                        'status' => strtoupper($paymentStatus),
                    ], JSON_THROW_ON_ERROR),
                ]);
            }
        }

        $this->command->info('Dummy transaksi realistis berhasil dibuat untuk tahun 2026-2030.');
    }
}