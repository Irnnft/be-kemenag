<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Dropping all tables...\n";
    Schema::disableForeignKeyConstraints();
    
    $tables = DB::select('SHOW TABLES');
    $colname = 'Tables_in_' . env('DB_DATABASE', 'db_laporan_kemenag');
    
    foreach ($tables as $table) {
        $name = $table->$colname;
        Schema::drop($name);
        echo "Dropped $name\n";
    }
    
    Schema::enableForeignKeyConstraints();
    echo "All tables dropped successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
