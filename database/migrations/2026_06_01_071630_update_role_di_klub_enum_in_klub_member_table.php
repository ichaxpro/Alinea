<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Expand role_di_klub ENUM to include 'owner' and 'admin'.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE klub_member MODIFY COLUMN role_di_klub ENUM('owner', 'admin', 'moderator', 'member') NOT NULL DEFAULT 'member'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back — existing 'owner'/'admin' rows will become empty string (MySQL warning)
        DB::statement("ALTER TABLE klub_member MODIFY COLUMN role_di_klub ENUM('moderator', 'member') NOT NULL DEFAULT 'member'");
    }
};
