<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('registration_type', ['participant', 'speaker']);
            $table->enum('modality', ['presencial', 'virtual', 'proyecto_aula'])->nullable();
            $table->string('ticket_code', 50)->unique();
            $table->timestamp('confirmed_at');
            $table->boolean('attended')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
