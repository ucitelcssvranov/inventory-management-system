<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Asset;

// Nájdeme assety s číslami na konci názvu
$assets = Asset::where('name', 'REGEXP', '#[0-9]+$')->get(['id', 'name', 'inventory_number']);

echo 'Assety s číslami na konci názvu:' . PHP_EOL;
echo str_repeat('=', 50) . PHP_EOL;

foreach ($assets as $asset) {
    echo 'ID: ' . $asset->id . ', Názov: "' . $asset->name . '", Inv. číslo: ' . $asset->inventory_number . PHP_EOL;
}

echo PHP_EOL . 'Celkom nájdených: ' . $assets->count() . ' assetov' . PHP_EOL;

// Teraz ukážeme aj assety s podobnými názvami
echo PHP_EOL . 'Analýza duplicitných názvov:' . PHP_EOL;
echo str_repeat('=', 50) . PHP_EOL;

$allAssets = Asset::all(['id', 'name']);
$groups = [];

foreach ($allAssets as $asset) {
    $baseName = preg_replace('/#\d+$/', '', $asset->name);
    if (!isset($groups[$baseName])) {
        $groups[$baseName] = [];
    }
    $groups[$baseName][] = $asset;
}

// Zobrazíme len skupiny s viac ako 1 assetom
foreach ($groups as $baseName => $assetsInGroup) {
    if (count($assetsInGroup) > 1) {
        echo "Základný názov: \"$baseName\" (" . count($assetsInGroup) . " assetov)" . PHP_EOL;
        foreach ($assetsInGroup as $asset) {
            echo "  - ID: {$asset->id}, Názov: \"{$asset->name}\"" . PHP_EOL;
        }
        echo PHP_EOL;
    }
}