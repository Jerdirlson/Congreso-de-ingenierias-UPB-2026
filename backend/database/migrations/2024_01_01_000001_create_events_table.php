<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'published', 'cancelled', 'finished'])->default('draft');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location')->nullable();
            $table->string('venue')->nullable();
            $table->string('cover_image')->nullable();
            $table->integer('max_participants')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
