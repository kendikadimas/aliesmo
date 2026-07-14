<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('biteship_order_id')->nullable()->after('courier');
            $table->string('biteship_tracking_id')->nullable()->after('biteship_order_id');
            $table->string('biteship_waybill_id')->nullable()->after('biteship_tracking_id');
            $table->string('biteship_status')->nullable()->after('biteship_waybill_id');
            $table->string('shipping_area_id')->nullable()->after('shipping_address');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'biteship_order_id',
                'biteship_tracking_id',
                'biteship_waybill_id',
                'biteship_status',
                'shipping_area_id',
            ]);
        });
    }
};
