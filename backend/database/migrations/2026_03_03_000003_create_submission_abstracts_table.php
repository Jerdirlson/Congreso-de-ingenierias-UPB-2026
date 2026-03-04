<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_abstracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('llm_axis_id')->nullable()->constrained('thematic_axes')->nullOnDelete();
            $table->text('content');
            $table->unsignedInteger('version');
            $table->enum('llm_status', ['pending', 'approved', 'rejected']);
            $table->decimal('llm_confidence_score', 5, 2)->nullable();
            $table->text('llm_justification')->nullable();
            $table->json('llm_raw_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_abstracts');
    }
};
