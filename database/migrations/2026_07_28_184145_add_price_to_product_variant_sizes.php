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
            // ponytail: nullable so existing rows keep working, app falls back to parent variant price
            $table->decimal('price', 12, 2)->nullable()->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('product_variant_sizes', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
