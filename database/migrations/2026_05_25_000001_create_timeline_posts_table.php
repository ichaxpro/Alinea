<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeline_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_klub')->nullable()->constrained('klub')->nullOnDelete();
            $table->string('judul_buku_dibahas', 120)->nullable();
            $table->text('pesan');
            $table->string('tag', 30)->nullable();
            // Stored path for media (relative to storage/app/public)
            $table->string('media')->nullable();
            // Media metadata
            $table->string('media_type')->nullable(); // image|video|file
            $table->string('media_original_name')->nullable();
            $table->unsignedBigInteger('media_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_posts');
    }
};