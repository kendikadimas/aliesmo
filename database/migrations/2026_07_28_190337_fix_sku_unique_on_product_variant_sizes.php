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
        Schema::table('product_variant_sizes', function (Blueprint $table) {
            $table->dropUnique('product_variant_sizes_sku_unique');
            // ponytail: SKU unik per variant, bukan global — produk berbeda boleh pakai SKU sama
            $table->unique(['variant_id', 'sku'], 'product_variant_sizes_variant_sku_unique');
        });
    }

    public function down(): void
    {
        Schema::table('product_variant_sizes', function (Blueprint $table) {
            $table->dropUnique('product_variant_sizes_variant_sku_unique');
            $table->unique('sku', 'product_variant_sizes_sku_unique');
        });
    }
};
