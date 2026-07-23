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
        Schema::table('orders', function (Blueprint $table) {
            // Kode layanan kurir dari Biteship (reg, ez, sps, dll)
            // Dibutuhkan untuk createOrder Biteship agar courier_type benar per kurir
            $table->string('courier_service')->nullable()->after('courier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('courier_service');
        });
    }
};
