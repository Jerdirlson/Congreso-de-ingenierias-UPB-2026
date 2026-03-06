<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('congress_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->enum('modality', ['presencial', 'virtual', 'hibrido'])->default('presencial');
            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('speaker')->nullable();
            $table->string('category')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('COP');
            $table->boolean('is_free')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('congress_event_id')
                ->nullable()
                ->after('submission_id')
                ->constrained('congress_events')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\CongressEvent::class);
            $table->dropColumn('congress_event_id');
        });

        Schema::dropIfExists('congress_events');
    }
};
