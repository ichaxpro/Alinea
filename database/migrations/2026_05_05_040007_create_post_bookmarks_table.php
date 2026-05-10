<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel post_bookmarks untuk fitur bookmark/simpan post di Timeline.
     * Di frontend, setiap post memiliki tombol bookmark yang bisa di-toggle.
     */
    public function up(): void
    {
        Schema::create('post_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('user')->onDelete('cascade');
            $table->foreignId('id_post')->constrained('timeline_posts')->onDelete('cascade');
            $table->timestamp('bookmarked_at')->useCurrent();

            // Satu user hanya bisa bookmark satu post sekali
            $table->unique(['id_user', 'id_post']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_bookmarks');
    }
};
