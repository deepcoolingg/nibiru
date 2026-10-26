<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->string('payment_method');
            $table->string('gateway')->comment('Xendit');
            $table->string('gateway_transaction_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('raw_response')->nullable()->comment('Simpan webhook response dari gateway');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};