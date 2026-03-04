<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->string('cloudflare_uid');
            $table->string('cloudflare_playback_url', 500);
            $table->string('cloudflare_thumbnail_url', 500)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->enum('status', ['pending', 'processing', 'ready', 'error']);
            $table->text('error_message')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('ready_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_videos');
    }
};
