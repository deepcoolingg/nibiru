<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('Password123!');

        User::query()->updateOrCreate(
            ['email' => 'admin@nibiru.test'],
            [
                'name' => 'Rina Pratama',
                'phone_number' => '081234567890',
                'birthday' => '1992-04-15',
                'password' => $defaultPassword,
                'google_id' => null,
                'role' => 'admin',
                'is_active' => true,
            ],
        );

        $baseCustomers = [
            ['name' => 'Aulia Rahman', 'email' => 'aulia.rahman@nibiru.test', 'phone_number' => '081211223344', 'birthday' => '1998-07-12'],
            ['name' => 'Nadia Putri', 'email' => 'nadia.putri@nibiru.test', 'phone_number' => '081322334455', 'birthday' => '2000-11-03'],
            ['name' => 'Fajar Saputra', 'email' => 'fajar.saputra@nibiru.test', 'phone_number' => '081433445566', 'birthday' => '1995-01-27'],
            ['name' => 'Maya Kartika', 'email' => 'maya.kartika@nibiru.test', 'phone_number' => '081544556677', 'birthday' => '1997-03-08'],
            ['name' => 'Dian Lestari', 'email' => 'dian.lestari@nibiru.test', 'phone_number' => '081655667788', 'birthday' => '1999-09-21'],
            ['name' => 'Bagas Wicaksono', 'email' => 'bagas.wicaksono@nibiru.test', 'phone_number' => '081766778899', 'birthday' => '1994-06-30'],
            ['name' => 'Siti Aisyah', 'email' => 'siti.aisyah@nibiru.test', 'phone_number' => '081877889900', 'birthday' => '2001-12-19'],
            ['name' => 'Rizky Maulana', 'email' => 'rizky.maulana@nibiru.test', 'phone_number' => '081988990011', 'birthday' => '1996-02-14'],
        ];

        foreach ($baseCustomers as $customer) {
            User::query()->updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'phone_number' => $customer['phone_number'],
                    'birthday' => $customer['birthday'],
                    'password' => $defaultPassword,
                    'google_id' => null,
                    'role' => 'customer',
                    'is_active' => true,
                ],
            );
        }

        $targetCustomerCount = 30;
        $existingCustomerCount = User::query()->where('role', 'customer')->count();

        if ($existingCustomerCount < $targetCustomerCount) {
            User::factory()
                ->count($targetCustomerCount - $existingCustomerCount)
                ->create([
                    'password' => $defaultPassword,
                    'role' => 'customer',
                    'is_active' => true,
                ]);
        }

        User::query()
            ->where(function ($query): void {
                $query->whereNull('phone_number')->orWhere('phone_number', '');
            })
            ->get()
            ->each(function (User $user): void {
                $user->forceFill([
                    'phone_number' => fake()->numerify('08##########'),
                ])->saveQuietly();
            });
    }
}
