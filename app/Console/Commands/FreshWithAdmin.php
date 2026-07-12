<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class FreshWithAdmin extends Command
{
    protected $signature   = 'db:fresh-admin
                                {--force : Bypass konfirmasi (wajib di production)}';

    protected $description = 'migrate:fresh + seed akun admin cs@aliesmo.id (HAPUS SEMUA DATA)';

    public function handle(): int
    {
        $isProduction = app()->environment('production');

        if ($isProduction && !$this->option('force')) {
            $this->error('Kamu sedang di PRODUCTION. Gunakan --force untuk melanjutkan.');
            $this->warn('  php artisan db:fresh-admin --force');
            return self::FAILURE;
        }

        if ($isProduction) {
            $this->warn('⚠  PRODUCTION — semua data akan dihapus permanen!');
        }

        if (!$this->confirm('Ini akan MENGHAPUS SEMUA DATA dan membuat ulang database. Lanjutkan?', false)) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        $this->info('Menjalankan migrate:fresh...');
        $this->call('migrate:fresh', ['--force' => true]);

        $this->info('Menyeeding akun admin...');
        $this->call('db:seed', [
            '--class' => 'AdminSeeder',
            '--force' => true,
        ]);

        $this->newLine();
        $this->info('Selesai!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Email',    'cs@aliesmo.id'],
                ['Password', 'aliesmopemalang'],
                ['Role',     'Admin'],
            ]
        );

        return self::SUCCESS;
    }
}
