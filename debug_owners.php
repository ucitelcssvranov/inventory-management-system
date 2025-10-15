<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';  
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing AssetController logic:\n\n";

// Simulujeme logiku z AssetController
$owners = \App\Models\Asset::whereNotNull('owner')
              ->distinct()
              ->pluck('owner')
              ->sort()
              ->values();

echo "Owners count: " . $owners->count() . "\n";
echo "Owners content:\n";
$owners->each(function($owner, $index) {
    echo "[$index] => '$owner'\n";
});

echo "\nTesting with empty values:\n";
$ownersWithEmpty = \App\Models\Asset::where('owner', '!=', '')
                                   ->whereNotNull('owner')
                                   ->distinct()
                                   ->pluck('owner')
                                   ->sort()
                                   ->values();

echo "Owners without empty count: " . $ownersWithEmpty->count() . "\n";
$ownersWithEmpty->each(function($owner, $index) {
    echo "[$index] => '$owner'\n";
});

echo "\nChecking for empty or whitespace-only owners:\n";
$allOwners = \App\Models\Asset::whereNotNull('owner')->pluck('owner');
$emptyCount = $allOwners->filter(function($owner) {
    return empty(trim($owner));
})->count();

echo "Assets with empty/whitespace owners: $emptyCount\n";
echo "Assets with non-empty owners: " . ($allOwners->count() - $emptyCount) . "\n";