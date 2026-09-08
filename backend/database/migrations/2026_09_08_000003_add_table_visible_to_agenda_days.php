<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_days', function (Blueprint $table) {
            $table->boolean('table_visible')->default(true)->after('poster_visible');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_days', function (Blueprint $table) {
            $table->dropColumn('table_visible');
        });
    }
};
