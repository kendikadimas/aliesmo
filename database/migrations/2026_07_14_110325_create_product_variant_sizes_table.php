<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('name'); // S, M, L, XL, XXL, etc.
            $table->integer('stock')->default(0);
            $table->string('sku')->nullable()->unique();
            $table->integer('weight')->nullable(); // gram, override dari variant jika ada
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['variant_id', 'is_active']);
        });

        // Add size_id and size_name to order_items for order snapshots
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('size_id')
                ->nullable()
                ->after('variant_id')
                ->constrained('product_variant_sizes')
                ->nullOnDelete();
            $table->string('size_name')->nullable()->after('variant_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['size_id']);
            $table->dropColumn(['size_id', 'size_name']);
        });

        Schema::dropIfExists('product_variant_sizes');
    }
};
