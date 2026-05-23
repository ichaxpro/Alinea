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
        Schema::table('featured_books', function (Blueprint $table) {
            $table->string('penerbit')->nullable()->after('penulis');
            $table->string('isbn')->nullable()->after('tahun');
            $table->integer('jumlah_halaman')->nullable()->after('isbn');
            $table->string('bahasa')->nullable()->default('Indonesia')->after('jumlah_halaman');
            $table->string('kategori')->nullable()->after('bahasa');
            $table->string('status')->default('tersedia')->after('kategori');
            $table->decimal('rating_avg', 3, 2)->default(0)->after('status');
            $table->integer('rating_count')->default(0)->after('rating_avg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('featured_books', function (Blueprint $table) {
            $table->dropColumn(['penerbit', 'isbn', 'jumlah_halaman', 'bahasa', 'kategori', 'status', 'rating_avg', 'rating_count']);
        });
    }
};
