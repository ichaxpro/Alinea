<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeline_attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->string('path');
            $table->string('type', 20)->nullable();
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id', 'sort_order'], 'timeline_attach_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_attachments');
    }
};