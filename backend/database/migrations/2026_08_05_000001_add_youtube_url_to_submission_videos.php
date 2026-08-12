<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La videoponencia deja de subirse como archivo: ahora el ponente comparte el
 * enlace de YouTube, que es el que se embebe/transmite el día del congreso.
 * Las columnas del archivo se conservan (ya son nullable) porque los videos
 * que se alcanzaron a subir no se borran nunca.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_videos', function (Blueprint $table) {
            $table->string('youtube_url', 500)->nullable()->after('submission_id');
        });
    }

    public function down(): void
    {
        Schema::table('submission_videos', function (Blueprint $table) {
            $table->dropColumn('youtube_url');
        });
    }
};
