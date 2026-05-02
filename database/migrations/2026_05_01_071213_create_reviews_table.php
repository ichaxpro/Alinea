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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
        $table->foreignId('id_user')->constrained('user')->onDelete('cascade');
        $table->foreignId('id_buku')->constrained('books')->onDelete('cascade');
        $table->integer('rating')->range(1, 5); // Range 1-5 bintang
        $table->text('ulasan');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
