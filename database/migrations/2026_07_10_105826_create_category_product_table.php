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
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'product_id']);
        });

        // Migrate existing category_id data to pivot table
        DB::statement('
            INSERT INTO category_product (category_id, product_id)
            SELECT category_id, id FROM products
            WHERE category_id IS NOT NULL
        ');

        // Remove category_id column from products (drop FK first, then index, then column)
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id', 'is_active']);
            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add category_id to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete()->after('id');
            $table->index(['category_id', 'is_active']);
        });

        // Migrate first category back (best-effort rollback)
        DB::statement('
            UPDATE products p
            SET category_id = (
                SELECT category_id FROM category_product
                WHERE product_id = p.id
                LIMIT 1
            )
        ');

        Schema::dropIfExists('category_product');
    }
};
