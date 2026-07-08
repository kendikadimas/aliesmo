<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ambil data bank existing
        $bankName    = DB::table('site_settings')->where('key', 'payment_bank_name')->value('value');
        $bankNo      = DB::table('site_settings')->where('key', 'payment_bank_account_no')->value('value');
        $bankOwner   = DB::table('site_settings')->where('key', 'payment_bank_account_name')->value('value');

        // Migrate ke format JSON array
        $banks = [];
        if ($bankName || $bankNo || $bankOwner) {
            $banks[] = [
                'bank_name'    => $bankName ?? '',
                'account_no'   => $bankNo ?? '',
                'account_name' => $bankOwner ?? '',
            ];
        }

        // Hapus key lama
        DB::table('site_settings')->whereIn('key', [
            'payment_bank_name',
            'payment_bank_account_no',
            'payment_bank_account_name',
        ])->delete();

        // Insert key baru payment_banks (json)
        DB::table('site_settings')->insert([
            'key'   => 'payment_banks',
            'value' => json_encode($banks),
            'type'  => 'json',
            'group' => 'payment',
            'label' => 'Daftar Rekening Bank',
        ]);

        // Update payment_qris_image type jadi text (path file)
        DB::table('site_settings')
            ->where('key', 'payment_qris_image')
            ->update(['type' => 'text', 'label' => 'Gambar QRIS']);
    }

    public function down(): void
    {
        // Ambil data banks dari json
        $banksJson = DB::table('site_settings')->where('key', 'payment_banks')->value('value');
        $banks = json_decode($banksJson, true);
        $first = $banks[0] ?? [];

        // Restore key lama
        DB::table('site_settings')->insert([
            ['key' => 'payment_bank_name',         'value' => $first['bank_name'] ?? '',    'type' => 'text', 'group' => 'payment', 'label' => 'Nama Bank'],
            ['key' => 'payment_bank_account_no',   'value' => $first['account_no'] ?? '',   'type' => 'text', 'group' => 'payment', 'label' => 'Nomor Rekening'],
            ['key' => 'payment_bank_account_name', 'value' => $first['account_name'] ?? '', 'type' => 'text', 'group' => 'payment', 'label' => 'Nama Pemilik Rekening'],
        ]);

        DB::table('site_settings')->where('key', 'payment_banks')->delete();
    }
};
