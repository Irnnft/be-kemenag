<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL specific way to update ENUM columns via raw SQL
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('operator_sekolah', 'kasi_penmad', 'staff_penmad') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('operator_sekolah', 'kasi_penmad') NOT NULL");
    }
};
