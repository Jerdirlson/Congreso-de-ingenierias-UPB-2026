<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_days', function (Blueprint $table) {
            $table->string('nombre', 40)->nullable()->after('dia');   // p. ej. "Miércoles"
            $table->string('fecha', 20)->nullable()->after('nombre'); // p. ej. "14"
            $table->string('mes', 30)->nullable()->after('fecha');    // p. ej. "octubre"
        });

        // Backfill con las etiquetas actuales de cada jornada.
        $defaults = [
            'mie' => ['Miércoles', '14', 'octubre'],
            'jue' => ['Jueves', '15', 'octubre'],
            'vie' => ['Viernes', '16', 'octubre'],
            'sab' => ['Sábado', '17', 'octubre'],
        ];
        foreach ($defaults as $dia => [$nombre, $fecha, $mes]) {
            DB::table('agenda_days')->where('dia', $dia)->update(compact('nombre', 'fecha', 'mes'));
        }
    }

    public function down(): void
    {
        Schema::table('agenda_days', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'fecha', 'mes']);
        });
    }
};
