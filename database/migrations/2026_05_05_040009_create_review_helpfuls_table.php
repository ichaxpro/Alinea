<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel review_helpfuls untuk fitur voting "Membantu" di halaman Ulasan Detail.
     * User bisa menandai ulasan orang lain sebagai "membantu".
     * Data ini juga digunakan untuk sorting ulasan "Paling Membantu".
     */
    public function up(): void
    {
        Schema::create('review_helpfuls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('user')->onDelete('cascade');
            $table->foreignId('id_review')->constrained('reviews')->onDelete('cascade');
            $table->timestamp('marked_at')->useCurrent();

            // Satu user hanya bisa vote "membantu" sekali per review
            $table->unique(['id_user', 'id_review']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_helpfuls');
    }
};
