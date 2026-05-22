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
        Schema::create('personal_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('judul');
            $table->string('penulis');
            $table->string('isbn')->nullable();
            $table->integer('tahun_terbit')->nullable();
            $table->string('kategori')->default('Fiksi');
            $table->string('cover_url')->nullable();
            $table->integer('jumlah_halaman')->nullable();
            $table->boolean('is_available')->default(true);
            $table->string('status')->default('tersedia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_books');
    }
};
