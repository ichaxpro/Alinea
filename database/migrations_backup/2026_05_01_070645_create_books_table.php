<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
        $table->foreignId('id_pemilik')->constrained('user')->onDelete('cascade');
        $table->string('judul');
        $table->string('penulis');
        $table->string('penerbit')->nullable();
        $table->year('tahun_terbit');
        $table->string('sinopsis', 200);
        $table->string('isbn', 20);
        $table->string('foto_sampul');
        $table->enum('status', ['tersedia', 'dipinjam'])->default('tersedia');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
