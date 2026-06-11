<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Achievement;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Achievement::where('key', 'rajin_membaca')->update([
            'criteria_type' => 'reading_history_count'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Achievement::where('key', 'rajin_membaca')->update([
            'criteria_type' => 'personal_book_count'
        ]);
    }
};
