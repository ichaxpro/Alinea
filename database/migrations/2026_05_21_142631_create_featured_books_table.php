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
        Schema::create('featured_books', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('penulis');
            $table->integer('tahun')->nullable();
            $table->string('sinopsis')->nullable();
            $table->json('genres')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('gradient_from')->default('#C7E7FF');
            $table->string('gradient_to')->default('#FFDDAF');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_books');
    }
};
