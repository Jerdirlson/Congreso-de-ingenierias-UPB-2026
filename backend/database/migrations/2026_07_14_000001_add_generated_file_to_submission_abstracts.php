<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Documento reconstruido sobre la plantilla oficial para resúmenes cuyo
     * archivo original no se guardó (subidos antes del 10 jul 2026).
     * generated_path nunca reemplaza stored_path: el original, si existe, manda.
     */
    public function up(): void
    {
        Schema::table('submission_abstracts', function (Blueprint $table) {
            $table->string('generated_path', 500)->nullable()->after('file_size');
            $table->json('template_problems')->nullable()->after('generated_path');
        });
    }

    public function down(): void
    {
        Schema::table('submission_abstracts', function (Blueprint $table) {
            $table->dropColumn(['generated_path', 'template_problems']);
        });
    }
};
