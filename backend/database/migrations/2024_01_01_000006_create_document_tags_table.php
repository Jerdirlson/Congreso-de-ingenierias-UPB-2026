<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('document_tag', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_tag_id')->constrained('document_tags')->cascadeOnDelete();
            $table->primary(['document_id', 'document_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_tag');
        Schema::dropIfExists('document_tags');
    }
};
