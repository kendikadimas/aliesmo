<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetupBiteshipWebhook extends Command
{
    protected $signature = 'biteship:setup-webhook
                            {--generate-secret : Generate BITESHIP_WEBHOOK_SECRET baru dan tampilkan}';

    protected $description = 'Tampilkan panduan & nilai siap copy-paste untuk setup webhook Biteship di dashboard';

    public function handle(): int
    {
        $appUrl      = rtrim(config('app.url', 'https://yourdomain.com'), '/');
        $webhookUrl  = $appUrl . '/api/v1/webhooks/biteship';
        $secret      = config('services.biteship.webhook_secret');
        $apiKey      = config('services.biteship.api_key');
        $isSandbox   = str_starts_with((string) $apiKey, 'biteship_test.');
        $env         = $isSandbox ? 'SANDBOX (Development)' : 'PRODUCTION';

        $this->newLine();
        $this->line('┌─────────────────────────────────────────────────────────┐');
        $this->line('│           SETUP WEBHOOK BITESHIP - ' . str_pad($env, 20) . '│');
        $this->line('└─────────────────────────────────────────────────────────┘');
        $this->newLine();

        // --- Cek APP_URL ---
        if (str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            $this->warn('⚠  APP_URL masih localhost. Biteship tidak bisa reach URL ini dari internet.');
            $this->line('   Untuk development: jalankan ngrok lalu update APP_URL di .env.');
            $this->line('   Untuk production: pastikan APP_URL sudah domain publik.');
            $this->newLine();
        }

        // --- Cek webhook secret ---
        $generateNew = $this->option('generate-secret');
        if ($generateNew || !$secret) {
            $secret = Str::random(40);
            $this->warn('  BITESHIP_WEBHOOK_SECRET belum diset atau --generate-secret dipasang.');
            $this->line('  Tambahkan baris berikut ke .env kamu:');
            $this->newLine();
            $this->line("  <fg=green>BITESHIP_WEBHOOK_SECRET={$secret}</>");
            $this->newLine();
            $this->line('  Lalu jalankan: php artisan config:clear');
            $this->newLine();
        }

        // --- Form values ---
        $this->info('Buka dashboard Biteship:');
        $this->line('  https://dashboard.biteship.com/integrations');
        $this->newLine();
        $this->line('Klik "Pengaturan" → "Tambah Webhook" → isi form berikut:');
        $this->newLine();

        $this->table(
            ['Field', 'Nilai'],
            [
                ['Nama Webhook',        config('app.name', 'Aliesmo') . ' Webhook'],
                ['Deskripsi',           'Order status, price & waybill updates dari ' . config('app.name', 'Aliesmo')],
                ['URL Webhook',         $webhookUrl],
                ['Events',              'order.status, order.price, order.waybill_id'],
                ['Headers Sig. Key',    'Authorization'],
                ['Headers Sig. Secret', $secret ?? '(belum diset — jalankan dengan --generate-secret)'],
            ]
        );

        $this->newLine();
        $this->line('┌─ Events yang harus dicentang: ─────────────────────────┐');
        $this->line('│  ✓ order.status    — update status pengiriman           │');
        $this->line('│  ✓ order.price     — update ongkir jika berat berbeda   │');
        $this->line('│  ✓ order.waybill_id — update nomor resi                 │');
        $this->line('└────────────────────────────────────────────────────────┘');
        $this->newLine();

        // --- Verifikasi webhook URL accessible ---
        $this->line('Verifikasi endpoint webhook kamu aktif:');
        $this->line("  curl -X POST {$webhookUrl} -H 'Content-Type: application/json' -d '{\"event\":\"ping\"}'");
        $this->newLine();

        // --- Instruksi akhir ---
        if ($isSandbox) {
            $this->warn('Mode SANDBOX aktif. Pastikan isi "Webhook URL (Test)" di dashboard, bukan production.');
        } else {
            $this->info('Mode PRODUCTION aktif. Isi "Webhook URL" utama di dashboard.');
        }

        $this->newLine();
        $this->line('Setelah webhook disimpan, test dengan simulasi status order di:');
        $this->line('  https://dashboard.biteship.com/orders');
        $this->newLine();

        return self::SUCCESS;
    }
}
