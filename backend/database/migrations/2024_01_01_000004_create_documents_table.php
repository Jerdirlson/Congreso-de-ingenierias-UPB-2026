<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('abstract')->nullable();
            $table->enum('status', ['draft', 'under_review', 'published', 'rejected', 'archived'])->default('draft');
            $table->enum('visibility', ['public', 'registered', 'private'])->default('public');

            // Relations
            $table->foreignId('category_id')->nullable()->constrained('document_categories')->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users');

            // File info (managed by Spatie Media Library)
            $table->string('file_type')->nullable();       // pdf, docx, pptx...
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->integer('page_count')->nullable();

            // Metadata
            $table->string('language', 5)->default('es');
            $table->string('doi')->nullable();
            $table->year('publication_year')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
