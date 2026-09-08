<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_days', function (Blueprint $table) {
            $table->id();
            $table->string('dia', 10)->unique();          // mie | jue | vie | sab
            $table->string('titulo')->nullable();          // subtítulo editable de la jornada
            $table->string('poster_path')->nullable();     // ruta pública del póster subido (null = usa el por defecto)
            $table->boolean('poster_visible')->default(true);
            $table->timestamps();
        });

        // Sembrar las 4 jornadas con su título actual y el póster visible por defecto.
        $now = now();
        DB::table('agenda_days')->insert([
            ['dia' => 'mie', 'titulo' => 'Transformación Digital y Tecnología Humanocéntrica', 'poster_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['dia' => 'jue', 'titulo' => 'Tecnologías Emergentes y Sociedad', 'poster_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['dia' => 'vie', 'titulo' => 'Sostenibilidad, Inteligencia Avanzada y Redes del Futuro', 'poster_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['dia' => 'sab', 'titulo' => 'Integración y Salud', 'poster_visible' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_days');
    }
};
