<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel post_likes untuk fitur like di Timeline.
     * Di frontend, setiap post menampilkan jumlah like ("50K", "28K")
     * dan tombol like bisa di-toggle (liked/unliked).
     */
    public function up(): void
    {
        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('user')->onDelete('cascade');
            $table->foreignId('id_post')->constrained('timeline_posts')->onDelete('cascade');
            $table->timestamp('liked_at')->useCurrent();

            // Satu user hanya bisa like satu post sekali
            $table->unique(['id_user', 'id_post']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_likes');
    }
};
