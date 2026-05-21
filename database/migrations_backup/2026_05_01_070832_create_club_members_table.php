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
        Schema::create('club_members', function (Blueprint $table) {
        $table->id();
        $table->foreignId('id_klub')->constrained('book_clubs')->onDelete('cascade');
        $table->foreignId('id_user')->constrained('user')->onDelete('cascade');
        $table->enum('role_di_klub', ['moderator', 'member'])->default('member');
        $table->timestamp('joined_at')->useCurrent();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_members');
    }
};
