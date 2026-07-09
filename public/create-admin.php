<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check existing admins
$admins = \App\Models\User::where('role', 'admin')->get(['id', 'name', 'email']);

if ($admins->isEmpty()) {
    // Create admin user
    $user = \App\Models\User::create([
        'name' => 'Admin Aliesmo',
        'email' => 'admin@aliesmo.id',
        'password' => bcrypt('Aliesmo2026!'),
        'email_verified_at' => now(),
    ]);
    // Force set role (not fillable for security)
    $user->role = 'admin';
    $user->save();
    echo json_encode(['status' => 'created', 'email' => $user->email]);
} else {
    echo json_encode(['status' => 'exists', 'admins' => $admins->toArray()]);
}
