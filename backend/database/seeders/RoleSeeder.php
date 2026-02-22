<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ──────────────────────────────────────────────────────────
        $admin          = Role::firstOrCreate(['name' => 'admin',          'guard_name' => 'web']);
        $administrativo = Role::firstOrCreate(['name' => 'administrativo', 'guard_name' => 'web']);
        $ponente        = Role::firstOrCreate(['name' => 'ponente',        'guard_name' => 'web']);
        $viewer         = Role::firstOrCreate(['name' => 'viewer',         'guard_name' => 'web']);

        // ── Usuarios por defecto ───────────────────────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@congreso.upb.edu.co'],
            ['name' => 'Admin Congreso', 'password' => Hash::make('Congreso2026!')]
        );
        $adminUser->syncRoles([$admin]);

        $admUser = User::firstOrCreate(
            ['email' => 'administrativo@congreso.upb.edu.co'],
            ['name' => 'Equipo Administrativo', 'password' => Hash::make('Congreso2026!')]
        );
        $admUser->syncRoles([$administrativo]);

        $ponenteUser = User::firstOrCreate(
            ['email' => 'ponente@congreso.upb.edu.co'],
            ['name' => 'Ponente Demo', 'password' => Hash::make('Congreso2026!')]
        );
        $ponenteUser->syncRoles([$ponente]);

        $viewerUser = User::firstOrCreate(
            ['email' => 'viewer@congreso.upb.edu.co'],
            ['name' => 'Asistente Demo', 'password' => Hash::make('Congreso2026!')]
        );
        $viewerUser->syncRoles([$viewer]);

        // Asignar rol admin al usuario ya existente si no tiene rol
        $existingAdmin = User::where('email', 'admin@congreso.edu.co')->first();
        if ($existingAdmin && $existingAdmin->roles->isEmpty()) {
            $existingAdmin->syncRoles([$admin]);
        }
    }
}
