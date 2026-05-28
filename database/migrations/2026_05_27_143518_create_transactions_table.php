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
            $table->foreignId('book_id')->constrained('personal_books')->onDelete('cascade');
            $table->foreignId('borrower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            
            $table->enum('status', ['pending', 'accepted', 'rejected', 'on_loan', 'returned'])->default('pending');
            
            $table->date('tanggal_pinjam_rencana')->nullable();
            $table->date('tanggal_kembali_rencana')->nullable();
            $table->date('tanggal_pengembalian_aktual')->nullable();
            $table->string('titik_temu')->nullable();
            
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
