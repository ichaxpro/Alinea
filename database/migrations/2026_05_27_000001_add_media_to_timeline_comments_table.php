<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timeline_comments', function (Blueprint $table) {
            $table->string('media')->nullable()->after('isi_komentar');
            $table->string('media_type', 20)->nullable()->after('media');
            $table->string('media_original_name')->nullable()->after('media_type');
            $table->unsignedBigInteger('media_size')->nullable()->after('media_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('timeline_comments', function (Blueprint $table) {
            $table->dropColumn([
                'media',
                'media_type',
                'media_original_name',
                'media_size',
            ]);
        });
    }
};
