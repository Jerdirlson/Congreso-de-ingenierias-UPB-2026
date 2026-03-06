<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_videos', function (Blueprint $table) {
            $table->string('stored_path', 500)->nullable()->after('submission_id');
            $table->string('original_filename')->nullable()->after('stored_path');
            $table->string('mime_type', 100)->nullable()->after('original_filename');
            $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');

            // Make Cloudflare fields optional (not used for local storage)
            $table->string('cloudflare_uid')->nullable()->change();
            $table->string('cloudflare_playback_url', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('submission_videos', function (Blueprint $table) {
            $table->dropColumn(['stored_path', 'original_filename', 'mime_type', 'file_size']);
            $table->string('cloudflare_uid')->nullable(false)->change();
            $table->string('cloudflare_playback_url', 500)->nullable(false)->change();
        });
    }
};
