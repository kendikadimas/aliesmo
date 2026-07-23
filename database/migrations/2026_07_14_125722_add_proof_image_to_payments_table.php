<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('proof_image')->nullable()->after('raw_payload');
            $table->text('proof_note')->nullable()->after('proof_image');
            $table->timestamp('confirmed_at')->nullable()->after('proof_note');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['proof_image', 'proof_note', 'confirmed_at']);
        });
    }
};
