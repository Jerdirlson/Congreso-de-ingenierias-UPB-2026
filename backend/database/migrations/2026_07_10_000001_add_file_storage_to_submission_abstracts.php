<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_abstracts', function (Blueprint $table) {
            // Archivo original del resumen (nullable: los resúmenes históricos no lo tienen)
            $table->string('original_filename')->nullable()->after('version');
            $table->string('stored_path', 500)->nullable()->after('original_filename');
            $table->string('mime_type', 100)->nullable()->after('stored_path');
            $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('submission_abstracts', function (Blueprint $table) {
            $table->dropColumn(['original_filename', 'stored_path', 'mime_type', 'file_size']);
        });
    }
};
