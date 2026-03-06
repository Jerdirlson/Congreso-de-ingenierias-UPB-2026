<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.admin_email', env('ADMIN_EMAIL', 'admin@congreso.upb.edu.co'));
        $password = config('app.admin_password', env('ADMIN_PASSWORD', 'Congreso2026!'));

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name'               => 'Administrador Congreso',
                'password'           => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasVerifiedEmail()) {
            $admin->markEmailAsVerified();
        }

        $admin->syncRoles(['admin']);
    }
}
