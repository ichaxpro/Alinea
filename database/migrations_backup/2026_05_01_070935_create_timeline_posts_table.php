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
        Schema::create('timeline_posts', function (Blueprint $table) {
            $table->id();
        $table->foreignId('id_user')->constrained('user')->onDelete('cascade');
        $table->foreignId('id_klub')->nullable()->constrained('book_clubs')->onDelete('cascade');
        $table->string('judul_buku_dibahas')->nullable();
        $table->string('pesan', 280); // Batasan karakter sesuai instruksi
        $table->string('media')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_posts');
    }
};
