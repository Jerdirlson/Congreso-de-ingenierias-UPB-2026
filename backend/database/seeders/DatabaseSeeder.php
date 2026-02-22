<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use App\Models\Event;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // ── Admin user (legacy) ────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@congreso.edu.co'],
            [
                'name'     => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        // ── Document categories ────────────────────────────────────────────
        $categories = [
            ['name' => 'Ponencias',       'slug' => 'ponencias',       'icon' => '📄', 'color' => '#6366f1'],
            ['name' => 'Artículos',       'slug' => 'articulos',       'icon' => '📰', 'color' => '#0ea5e9'],
            ['name' => 'Posters',         'slug' => 'posters',         'icon' => '🖼️', 'color' => '#f59e0b'],
            ['name' => 'Memorias',        'slug' => 'memorias',        'icon' => '📚', 'color' => '#10b981'],
            ['name' => 'Presentaciones',  'slug' => 'presentaciones',  'icon' => '📊', 'color' => '#ec4899'],
        ];

        foreach ($categories as $cat) {
            DocumentCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // ── Main event ────────────────────────────────────────────────────
        Event::firstOrCreate(
            ['slug' => 'congreso-ingenierias-2026'],
            [
                'title'       => 'Congreso de Ingenierías 2026',
                'description' => 'Evento académico anual de la facultad de ingenierías.',
                'status'      => 'published',
                'start_date'  => '2026-04-15',
                'end_date'    => '2026-04-17',
                'location'    => 'Colombia',
                'venue'       => 'Auditorio Principal',
            ]
        );
    }
}
