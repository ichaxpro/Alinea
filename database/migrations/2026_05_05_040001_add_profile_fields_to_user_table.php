<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom profil yang dibutuhkan frontend:
     * - username  → ditampilkan di Timeline (@isoba__, @dina_r)
     * - foto_profil → avatar user di Timeline & Profile
     * - bio → teks bio di halaman Profile ("Apaan Nih?!")
     */
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('username', 30)->unique()->after('nama');
            $table->string('foto_profil')->nullable()->after('kota');
            $table->text('bio')->nullable()->after('foto_profil');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['username', 'foto_profil', 'bio']);
        });
    }
};
