<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ŠTRUKTÚRA TABUĽKY inventory_counts ===\n\n";

$columns = DB::select('DESCRIBE inventory_counts');

foreach($columns as $column) {
    echo sprintf("%-25s | %-20s | %-10s | %-10s | %-10s | %s\n", 
        $column->Field, 
        $column->Type, 
        $column->Null, 
        $column->Key, 
        $column->Default ?? 'NULL',
        $column->Extra ?? ''
    );
}

echo "\n=== UKONČENÉ ===\n";