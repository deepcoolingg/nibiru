<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number')->unique();
            $table->uuid('user_id')->nullable()->comment('Null kalau guest checkout');
            $table->uuid('voucher_id')->nullable()->comment('Voucher yang dipakai');
            $table->string('recipient_name');
            $table->string('phone_number');
            $table->text('shipping_address');
            $table->string('country')->comment('Untuk filter Local/International');
            $table->string('order_type');
            $table->string('shipping_courier')->nullable()->comment('Misal: JNE, PosID, DHL');
            $table->string('tracking_number')->nullable()->comment('Nomor resi pengiriman');
            $table->string('currency');
            $table->decimal('exchange_rate', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('qty_discount_amount', 15, 2)->default(0);
            $table->decimal('voucher_discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total_cost_price', 15, 2)->comment('Total modal buat track margin');
            $table->decimal('total_profit', 15, 2)->comment('Margin Profit');
            $table->decimal('grand_total', 15, 2);
            $table->string('status')->default('pending')->comment('pending, paid, shipped, completed, canceled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};