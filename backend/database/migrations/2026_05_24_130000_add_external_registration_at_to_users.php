<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('external_registration_at')->nullable()->after('city');
            $table->timestamp('external_registration_paid_at')->nullable()->after('external_registration_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['external_registration_at', 'external_registration_paid_at']);
        });
    }
};
