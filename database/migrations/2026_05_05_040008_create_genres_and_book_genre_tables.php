<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel genres + pivot book_genre untuk mendukung genre buku multiple.
     * Di frontend Ulasan Detail, satu buku bisa punya beberapa genre:
     * misal "Pulang" punya genre "Horror" dan "Thriller".
     * Di halaman Pinjam, genre digunakan untuk filter buku.
     */
    public function up(): void
    {
        // Tabel master genre
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('nama_genre', 50)->unique();
        });

        // Tabel pivot many-to-many: books <-> genres
        Schema::create('book_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_buku')->constrained('books')->onDelete('cascade');
            $table->foreignId('id_genre')->constrained('genres')->onDelete('cascade');

            $table->unique(['id_buku', 'id_genre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_genre');
        Schema::dropIfExists('genres');
    }
};
