<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['scheduled', 'live', 'ended', 'cancelled'])->default('scheduled');

            // Relations
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('speaker_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');

            // Schedule
            $table->dateTime('scheduled_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();

            // Streaming config
            $table->string('stream_key')->nullable()->unique();  // RTMP key
            $table->string('rtmp_url')->nullable();
            $table->string('hls_url')->nullable();              // playback URL
            $table->string('platform')->nullable();             // youtube, twitch, custom...
            $table->string('platform_url')->nullable();         // external embed URL
            $table->enum('type', ['live', 'recorded', 'external'])->default('live');

            // Stats
            $table->unsignedInteger('peak_viewers')->default(0);
            $table->unsignedInteger('total_views')->default(0);

            // Chat
            $table->boolean('chat_enabled')->default(true);

            $table->string('thumbnail')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streams');
    }
};
