<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('original_filename');
            $table->string('stored_path', 500);
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type', 100);
            $table->enum('status', ['pending_review', 'under_review', 'revision_requested', 'approved']);
            $table->timestamp('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_documents');
    }
};
