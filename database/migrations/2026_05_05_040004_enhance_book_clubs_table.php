<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom pendukung di tabel book_clubs:
     * - jadwal: jadwal pertemuan rutin ("Setiap Sabtu, 19:00 WIB")
     * - foto_klub: gambar/cover klub (opsional, bisa pakai gradient di frontend)
     */
    public function up(): void
    {
        Schema::table('book_clubs', function (Blueprint $table) {
            $table->string('jadwal')->nullable()->after('deskripsi');
            $table->string('foto_klub')->nullable()->after('jadwal');
        });
    }

    public function down(): void
    {
        Schema::table('book_clubs', function (Blueprint $table) {
            $table->dropColumn(['jadwal', 'foto_klub']);
        });
    }
};
