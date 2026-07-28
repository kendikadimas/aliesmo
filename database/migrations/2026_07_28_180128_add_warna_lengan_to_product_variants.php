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
        Schema::table('product_variants', function (Blueprint $table) {
            // ponytail: nullable so existing rows don't break, name kolom tetap ada untuk backward compat
            $table->string('warna')->nullable()->after('name');
            $table->string('lengan')->nullable()->after('warna');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['warna', 'lengan']);
        });
    }
};
