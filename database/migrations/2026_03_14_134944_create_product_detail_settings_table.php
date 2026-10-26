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
        Schema::create('product_detail_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('description')->nullable();
            $table->json('description_highlights')->nullable();
            $table->string('size_fit_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_detail_settings');
    }
};
