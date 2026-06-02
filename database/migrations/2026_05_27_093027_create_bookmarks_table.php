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
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('book_identifier');
            $table->enum('identifier_type', ['db', 'google'])->default('db');
            $table->string('judul');
            $table->string('penulis')->nullable();
            $table->string('foto_sampul')->nullable();
            $table->string('kategori')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'book_identifier', 'identifier_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
