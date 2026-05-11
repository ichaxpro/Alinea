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
        $table->unsignedTinyInteger('rating'); // Range 1-5 bintang (validasi di level aplikasi)
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
