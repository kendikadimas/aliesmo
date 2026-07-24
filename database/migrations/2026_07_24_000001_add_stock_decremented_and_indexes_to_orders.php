<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('stock_decremented_at')->nullable()->after('paid_at');
            $table->index('biteship_order_id');
            $table->index('customer_email');
            $table->index('stock_decremented_at');
        });

        // Backfill: order yang statusnya sudah "stok pernah diambil" — cegah double-decrement
        \Illuminate\Support\Facades\DB::table('orders')
            ->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])
            ->whereNull('stock_decremented_at')
            ->update([
                'stock_decremented_at' => \Illuminate\Support\Facades\DB::raw('COALESCE(paid_at, updated_at, created_at)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['biteship_order_id']);
            $table->dropIndex(['customer_email']);
            $table->dropIndex(['stock_decremented_at']);
            $table->dropColumn('stock_decremented_at');
        });
    }
};
