<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom tag di timeline_posts.
     * Tag digunakan di Timeline sebagai label kategori post:
     * "Dibaca", "Selesai", "Kutipan", "Ulasan", "Dll"
     */
    public function up(): void
    {
        Schema::table('timeline_posts', function (Blueprint $table) {
            $table->string('tag', 30)->nullable()->after('pesan');
        });
    }

    public function down(): void
    {
        Schema::table('timeline_posts', function (Blueprint $table) {
            $table->dropColumn('tag');
        });
    }
};
