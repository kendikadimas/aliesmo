<?php
namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'cs@aliesmo.id';

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => 'Admin Aliesmo',
                'password' => Hash::make('aliesmopemalang'),
                'role'     => UserRole::Admin,
            ]
        );

        // Set email_verified_at via direct assignment (tidak ada di $fillable)
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        $this->command?->info("Admin seeded: {$email}");
    }
}
