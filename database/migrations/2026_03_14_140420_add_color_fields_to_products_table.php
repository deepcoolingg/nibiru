<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('color_name')->default('Default')->after('size_fit_image');
            $table->string('color_hex', 7)->default('#D9D9D9')->after('color_name');
        });

        DB::table('products')
            ->orderBy('created_at')
            ->get(['id'])
            ->each(function ($product): void {
                $firstColor = DB::table('product_colors')
                    ->where('product_id', $product->id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('created_at')
                    ->first(['name', 'hex_code']);

                if ($firstColor !== null) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'color_name' => $firstColor->name,
                            'color_hex' => $firstColor->hex_code,
                        ]);
                }
            });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['color_name', 'color_hex']);
        });
    }
};
