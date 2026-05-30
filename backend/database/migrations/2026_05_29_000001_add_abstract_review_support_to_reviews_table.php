<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('submission_document_id')->nullable()->change();
            $table->foreignId('submission_abstract_id')->nullable()->after('submission_document_id')->constrained('submission_abstracts')->cascadeOnDelete();
            $table->string('type')->default('document')->after('submission_abstract_id');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['submission_abstract_id']);
            $table->dropColumn(['submission_abstract_id', 'type']);
            $table->unsignedBigInteger('submission_document_id')->nullable(false)->change();
        });
    }
};
