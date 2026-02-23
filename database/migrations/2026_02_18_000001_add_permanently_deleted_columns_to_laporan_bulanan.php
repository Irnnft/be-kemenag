<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('laporan_bulanan', function (Blueprint $blueprint) {
            $blueprint->timestamp('permanently_deleted_at_operator')->nullable()->after('deleted_at_operator');
            $blueprint->timestamp('permanently_deleted_at_admin')->nullable()->after('deleted_at_admin');
        });
    }

    public function down()
    {
        Schema::table('laporan_bulanan', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['permanently_deleted_at_operator', 'permanently_deleted_at_admin']);
        });
    }
};
