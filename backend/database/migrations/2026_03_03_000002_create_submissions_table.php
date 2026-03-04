<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('thematic_axis_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 500);
            $table->string('status', 30)->default('draft');
            $table->enum('modality', ['presencial_oral', 'presencial_poster', 'virtual', 'proyecto_aula'])->nullable();
            $table->unsignedInteger('abstract_attempts')->default(0);
            $table->unsignedInteger('document_version')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('thematic_axis_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
