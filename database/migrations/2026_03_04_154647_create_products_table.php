<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->decimal('price', 12, 2);
            $table->decimal('cost_price', 12, 2)->comment('Harga modal buat ngitung margin profit');
            $table->integer('stock')->default(0);
            $table->decimal('weight', 8, 2)->comment('Dalam gram, buat ngitung ongkir');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};