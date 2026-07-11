<?php
/**
 * Mail test & debug script
 * Akses: https://aliesmo.id/mail-test.php?token=YOUR_DEPLOY_TOKEN
 *
 * HAPUS file ini setelah selesai debugging.
 */

// ── Auth ─────────────────────────────────────────────────────────────────────
$token = '';
$envFile = __DIR__ . '/../.env';
$envVars = [];

if (file_exists($envFile)) {
    foreach (file($envFile) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$key, $val] = array_pad(explode('=', $line, 2), 2, '');
        $envVars[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
    }
    $token = $envVars['DEPLOY_TOKEN'] ?? '';
}

$provided = $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? $_GET['token'] ?? '';
if (empty($token) || empty($provided) || !hash_equals($token, $provided)) {
    http_response_code(403);
    die('403 Forbidden');
}

// ── Bootstrap Laravel ─────────────────────────────────────────────────────────
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// ── Collect debug info ────────────────────────────────────────────────────────
$info = [
    'php_version'      => PHP_VERSION,
    'mail_mailer'      => config('mail.default'),
    'mail_host'        => config('mail.mailers.smtp.host'),
    'mail_port'        => config('mail.mailers.smtp.port'),
    'mail_scheme'      => config('mail.mailers.smtp.scheme') ?? '(not set)',
    'mail_username'    => config('mail.mailers.smtp.username'),
    'mail_from'        => config('mail.from.address'),
    'openssl_loaded'   => extension_loaded('openssl') ? 'yes' : 'NO — missing!',
    'socket_test'      => null,
    'send_result'      => null,
    'send_error'       => null,
];

// ── Test TCP socket ke mail server ────────────────────────────────────────────
$host = config('mail.mailers.smtp.host');
$port = (int) config('mail.mailers.smtp.port');

$socket = @fsockopen("ssl://{$host}", $port, $errno, $errstr, 5);
if ($socket) {
    fclose($socket);
    $info['socket_test'] = "OK — ssl://{$host}:{$port} terhubung";
} else {
    // Coba tanpa ssl:// prefix
    $socket2 = @fsockopen($host, $port, $errno2, $errstr2, 5);
    if ($socket2) {
        fclose($socket2);
        $info['socket_test'] = "PARTIAL — tcp://{$host}:{$port} OK tapi ssl:// gagal ({$errstr})";
    } else {
        $info['socket_test'] = "GAGAL — tidak bisa konek ke {$host}:{$port} | SSL error: {$errstr} ({$errno}) | TCP error: {$errstr2} ({$errno2})";
    }
}

// ── Coba kirim email test ─────────────────────────────────────────────────────
$target = $_GET['to'] ?? 'kalanalabs@gmail.com';

try {
    Illuminate\Support\Facades\Mail::raw(
        "Ini email test dari Aliesmo production server.\n\n"
        . "Waktu: " . now()->toDateTimeString() . "\n"
        . "Host: " . ($host ?? '-') . "\n"
        . "Port: " . ($port ?: '-') . "\n"
        . "Scheme: " . (config('mail.mailers.smtp.scheme') ?? 'tidak diset') . "\n"
        . "PHP: " . PHP_VERSION,
        function ($message) use ($target) {
            $message->to($target)
                    ->subject('[Aliesmo] Email Test - ' . now()->toDateTimeString());
        }
    );
    $info['send_result'] = "BERHASIL — email terkirim ke {$target}";
} catch (\Throwable $e) {
    $info['send_result'] = 'GAGAL';
    $info['send_error']  = $e->getMessage();

    // Coba lagi dengan port 587 + tls jika port 465 gagal
    if ($port === 465) {
        try {
            config([
                'mail.mailers.smtp.port'   => 587,
                'mail.mailers.smtp.scheme' => 'tls',
            ]);
            // Reset mailer supaya config baru dipakai
            app()->forgetInstance('swift.mailer');
            app()->forgetInstance('swift.transport');

            Illuminate\Support\Facades\Mail::forgetMailers();
            Illuminate\Support\Facades\Mail::raw(
                "Retry test via port 587/TLS dari Aliesmo.\nWaktu: " . now()->toDateTimeString(),
                function ($message) use ($target) {
                    $message->to($target)
                            ->subject('[Aliesmo] Email Test (587/TLS) - ' . now()->toDateTimeString());
                }
            );
            $info['send_result'] = "BERHASIL via port 587/TLS — email terkirim ke {$target}";
            $info['send_error']  = "Port 465 gagal, tapi 587/TLS sukses. Update MAIL_PORT=587 dan MAIL_SCHEME=tls di .env";
        } catch (\Throwable $e2) {
            $info['send_error'] .= "\n--- Retry 587/TLS juga gagal: " . $e2->getMessage();
        }
    }
}

// ── Output ────────────────────────────────────────────────────────────────────
header('Content-Type: text/plain; charset=utf-8');
echo "=== ALIESMO MAIL DEBUG ===\n\n";
foreach ($info as $k => $v) {
    printf("%-20s : %s\n", $k, $v ?? '(null)');
}
echo "\n=========================\n";
echo "Selesai. Hapus file ini setelah debug!\n";
