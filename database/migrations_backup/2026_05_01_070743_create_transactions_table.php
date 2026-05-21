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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
        $table->foreignId('id_peminjam')->constrained('user');
        $table->foreignId('id_pemilik')->constrained('user');
        $table->foreignId('id_buku')->constrained('books');
        $table->date('tanggal_pinjam')->nullable();
        $table->date('tanggal_kembali_rencana');
        $table->date('tanggal_pengembalian_aktual')->nullable();
        $table->text('titik_temu_pinjam'); 
        $table->text('titik_temu_kembali')->nullable();
        $table->enum('status_transaksi', ['pending', 'accepted', 'rejected', 'on_loan', 'returned', 'cancelled'])->default('pending');
        $table->text('alasan_penolakan')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
