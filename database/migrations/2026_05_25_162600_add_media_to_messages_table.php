<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('media_url')->nullable()->after('message');
            $table->enum('media_type', ['image', 'audio', 'video'])->nullable()->after('media_url');
            $table->string('media_original_name')->nullable()->after('media_type');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['media_url', 'media_type', 'media_original_name']);
        });
    }
};
