<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Admin Test',
                'email'    => 'admin@test.com',
                'role'     => 'admin',
            ],
            [
                'name'     => 'Administrativo Test',
                'email'    => 'administrativo@test.com',
                'role'     => 'administrativo',
            ],
            [
                'name'     => 'Revisor Test',
                'email'    => 'revisor@test.com',
                'role'     => 'revisor',
            ],
            [
                'name'     => 'Ponente Test',
                'email'    => 'ponente@test.com',
                'role'     => 'ponente',
            ],
            [
                'name'     => 'Participante Test',
                'email'    => 'participante@test.com',
                'role'     => 'participante',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make('123123123'),
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles([$data['role']]);
        }
    }
}
