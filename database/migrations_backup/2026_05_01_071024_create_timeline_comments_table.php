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
        Schema::create('timeline_comments', function (Blueprint $table) {
            $table->id();
        $table->foreignId('id_post')->constrained('timeline_posts')->onDelete('cascade');
        $table->foreignId('id_user')->constrained('user')->onDelete('cascade');
        $table->text('isi_komentar');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_comments');
    }
};
