<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_items', function (Blueprint $table) {
            $table->id();
            // Jornada: mie | jue | vie | sab (misma clave que los tabs públicos)
            $table->string('dia', 10)->index();
            // Bloque dentro del día: manana | tarde | noche (opcional)
            $table->string('bloque', 20)->nullable();
            // Tipo de actividad (define el ícono en la vista): registro, apertura,
            // conferencia, ponencia, foto, refrigerio, almuerzo, cultural, otro
            $table->string('tipo', 30)->default('otro');
            // Datos de la fila. Todas las columnas del "Excel"; casi todas opcionales
            // para que Liney llene solo lo que aplique a cada actividad.
            $table->string('hora', 40)->nullable();
            $table->string('titulo', 255);
            $table->string('ponente', 255)->nullable();
            $table->string('pais', 80)->nullable();
            $table->string('etiqueta', 80)->nullable();   // p. ej. "Conferencia Central"
            $table->text('descripcion')->nullable();       // título de la charla / detalle
            $table->string('lugar', 255)->nullable();
            // Orden manual dentro del día
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['dia', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_items');
    }
};
