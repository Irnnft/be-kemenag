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
        Schema::table('laporan_bulanan', function (Blueprint $table) {
            $table->timestamp('deleted_at_operator')->nullable()->after('updated_at');
            $table->timestamp('deleted_at_admin')->nullable()->after('deleted_at_operator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_bulanan', function (Blueprint $table) {
            $table->dropColumn(['deleted_at_operator', 'deleted_at_admin']);
        });
    }
};
