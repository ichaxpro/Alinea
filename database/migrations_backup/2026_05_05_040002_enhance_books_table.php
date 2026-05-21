<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyesuaikan tabel books agar match dengan halaman Ulasan Detail & Pinjam:
     * - sinopsis: dari varchar(200) → text, karena sinopsis di frontend bisa panjang
     * - jumlah_halaman: ditampilkan di Ulasan Detail ("406 Halaman")
     * - bahasa: ditampilkan di Ulasan Detail ("Indonesia")
     * - kategori: ditampilkan sebagai badge di Ulasan Detail ("Fiksi")
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Ubah sinopsis dari varchar(200) ke text agar bisa menampung sinopsis panjang
            $table->text('sinopsis')->change();

            $table->unsignedSmallInteger('jumlah_halaman')->nullable()->after('sinopsis');
            $table->string('bahasa', 50)->default('Indonesia')->after('jumlah_halaman');
            $table->string('kategori', 100)->nullable()->after('bahasa');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('sinopsis', 200)->change();
            $table->dropColumn(['jumlah_halaman', 'bahasa', 'kategori']);
        });
    }
};
