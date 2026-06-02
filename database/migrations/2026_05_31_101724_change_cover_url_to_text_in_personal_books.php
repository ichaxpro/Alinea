<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_books', function (Blueprint $table) {
            $table->text('cover_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('personal_books', function (Blueprint $table) {
            $table->string('cover_url', 255)->nullable()->change();
        });
    }
};
