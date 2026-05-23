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
        Schema::create('klub', function (Blueprint $table) {

    $table->id();
    $table->string('nama_klub');
    $table->string('kategori');
    $table->text('deskripsi');
    $table->string('foto_klub')->nullable();
    $table->string('gradient_from')->default('#FFDDAF');
    $table->string('gradient_to')->default('#C7E7FF');
    // Owner hanya 1 orang (yang membuat klub)
    $table->foreignId('id_owner')->constrained('users'); 
    $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
    $table->unsignedInteger('member_count')->default(0);
    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klub');
    }
};
