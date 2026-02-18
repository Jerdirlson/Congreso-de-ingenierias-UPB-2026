<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stream_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();     // bytes
            $table->unsignedInteger('duration')->nullable();         // seconds
            $table->string('format')->nullable();                    // mp4, hls
            $table->string('resolution')->nullable();                // 1080p, 720p
            $table->enum('status', ['processing', 'ready', 'failed'])->default('processing');
            $table->string('thumbnail')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->enum('visibility', ['public', 'registered', 'private'])->default('public');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_recordings');
    }
};
