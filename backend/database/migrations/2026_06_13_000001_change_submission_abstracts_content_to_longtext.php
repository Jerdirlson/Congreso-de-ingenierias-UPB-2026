<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_abstracts', function (Blueprint $table) {
            // TEXT (64 KB) era insuficiente para el texto extraído de documentos
            // largos: el insert fallaba (Data too long) y dejaba la ponencia
            // huérfana en estado abstract_submitted sin resumen.
            $table->longText('content')->change();
        });
    }

    public function down(): void
    {
        Schema::table('submission_abstracts', function (Blueprint $table) {
            $table->text('content')->change();
        });
    }
};
