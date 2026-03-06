<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ThematicAxisSeeder::class,
            AdminUserSeeder::class,
            RevisorUserSeeder::class,
            CongressEventSeeder::class,
        ]);
    }
}
