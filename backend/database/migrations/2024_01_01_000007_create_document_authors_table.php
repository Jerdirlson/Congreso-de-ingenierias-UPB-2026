<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot: a document can have multiple authors (speakers or users)
        Schema::create('document_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('speaker_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');          // denormalized for external authors
            $table->string('email')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_corresponding')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_authors');
    }
};
