<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel follows untuk sistem follower/following.
     * Digunakan di:
     * - Halaman Profile: menampilkan jumlah "256 Following" dan "165 Followers"
     * - Tab "Following" di Timeline: menampilkan post hanya dari user yang di-follow
     */
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_follower')->constrained('user')->onDelete('cascade');
            $table->foreignId('id_following')->constrained('user')->onDelete('cascade');
            $table->timestamp('followed_at')->useCurrent();

            // Satu user tidak bisa follow user yang sama dua kali
            $table->unique(['id_follower', 'id_following']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
