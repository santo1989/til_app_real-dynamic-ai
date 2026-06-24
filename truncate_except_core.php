<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Starting truncation...\n";

// Disable foreign key checks
db::statement('SET FOREIGN_KEY_CHECKS=0');

// Get all tables except the ones we want to keep
$keepTables = ['users', 'departments', 'financial_years', 'migrations'];
$allTables = DB::select('SHOW TABLES');
$databaseName = DB::getDatabaseName();
$keyName = 'Tables_in_' . $databaseName;

foreach ($allTables as $table) {
    $tableName = $table->$keyName;
    if (!in_array($tableName, $keepTables)) {
        echo "Truncating table: $tableName\n";
        DB::table($tableName)->truncate();
    }
}

// Re-enable foreign key checks
db::statement('SET FOREIGN_KEY_CHECKS=1');

echo "Truncation complete. Kept tables: " . implode(', ', $keepTables) . "\n";