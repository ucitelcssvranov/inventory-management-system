<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Asset;

// Nájdeme assety s číslami na konci názvu
$assets = Asset::where('name', 'REGEXP', '#[0-9]+$')->get();

echo 'Začínam čistenie názvov assetov s číslami na konci...' . PHP_EOL;
echo str_repeat('=', 60) . PHP_EOL;

$updatedCount = 0;

foreach ($assets as $asset) {
    $originalName = $asset->name;
    $cleanName = preg_replace('/#\d+$/', '', $asset->name);
    $cleanName = trim($cleanName); // odstránime aj medzery na konci
    
    if ($originalName !== $cleanName) {
        echo "ID: {$asset->id}" . PHP_EOL;
        echo "  Pôvodný názov: \"{$originalName}\"" . PHP_EOL;
        echo "  Nový názov:    \"{$cleanName}\"" . PHP_EOL;
        
        // Aktualizujeme názov
        $asset->name = $cleanName;
        $asset->save();
        
        $updatedCount++;
        echo "  ✓ Aktualizované!" . PHP_EOL . PHP_EOL;
    }
}

echo str_repeat('=', 60) . PHP_EOL;
echo "Celkom aktualizovaných: {$updatedCount} assetov" . PHP_EOL;
echo "Čistenie dokončené!" . PHP_EOL;