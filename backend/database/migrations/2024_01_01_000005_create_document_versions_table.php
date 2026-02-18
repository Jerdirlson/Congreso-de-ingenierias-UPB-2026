<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('version_number');          // e.g. "1.0", "2.1"
            $table->text('changelog')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
