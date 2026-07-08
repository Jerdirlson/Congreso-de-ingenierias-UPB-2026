<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Artículos para publicación en revista científica (carril paralelo,
        // separado de los resúmenes/documentos del flujo principal)
        Schema::create('submission_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('original_filename');
            $table->string('stored_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->string('status')->default('pending_review');
            $table->timestamp('submitted_at')->nullable();
        });

        // Opt-in del ponente: "quiero que mi trabajo sea considerado para revista"
        Schema::table('submissions', function (Blueprint $table) {
            $table->timestamp('journal_opt_in_at')->nullable()->after('document_version');
        });

        // Las revisiones pueden apuntar ahora también a un artículo
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('submission_article_id')->nullable()->after('submission_abstract_id')->constrained('submission_articles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['submission_article_id']);
            $table->dropColumn('submission_article_id');
        });
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('journal_opt_in_at');
        });
        Schema::dropIfExists('submission_articles');
    }
};
