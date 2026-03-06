<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RevisorUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Revisor Uno',
                'email'    => 'revisor1@congreso.upb.edu.co',
                'password' => 'Revisor2026!',
            ],
            [
                'name'     => 'Revisor Dos',
                'email'    => 'revisor2@congreso.upb.edu.co',
                'password' => 'Revisor2026!',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $user->syncRoles(['revisor']);
        }
    }
}
