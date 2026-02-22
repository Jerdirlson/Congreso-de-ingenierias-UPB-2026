<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('streams', function (Blueprint $table) {
            $table->string('cloudflare_uid')->nullable()->unique()->after('thumbnail');
            $table->string('cloudflare_video_uid')->nullable()->after('cloudflare_uid');
            $table->json('cloudflare_meta')->nullable()->after('cloudflare_video_uid');
        });
    }

    public function down(): void
    {
        Schema::table('streams', function (Blueprint $table) {
            $table->dropColumn(['cloudflare_uid', 'cloudflare_video_uid', 'cloudflare_meta']);
        });
    }
};
