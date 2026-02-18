<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot: speakers participating in events
        Schema::create('event_speaker', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('speaker_id')->constrained()->cascadeOnDelete();
            $table->string('role')->nullable();          // keynote, panelist, workshop...
            $table->string('session_title')->nullable();
            $table->primary(['event_id', 'speaker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_speaker');
    }
};
