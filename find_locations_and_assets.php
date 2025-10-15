<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Location;
use App\Models\Asset;

echo "=== HĽADANIE LOKÁCIÍ ===\n\n";

// Hľadáme lokácie
echo "📍 Hľadám lokácie obsahujúce '304':\n";
$locations304 = Location::where('name', 'like', '%304%')
                       ->orWhere('room_number', '304')
                       ->get();

foreach ($locations304 as $location) {
    echo "  - ID: {$location->id}, Názov: {$location->name}\n";
    if ($location->room_number) {
        echo "    Číslo miestnosti: {$location->room_number}\n";
    }
}

echo "\n📍 Hľadám lokácie obsahujúce 'Nová budova':\n";
$novaBudova = Location::where('name', 'like', '%Nová budova%')
                     ->orWhere('name', 'like', '%Nova budova%')
                     ->get();

foreach ($novaBudova as $location) {
    echo "  - ID: {$location->id}, Názov: {$location->name}\n";
}

// Pozrieme si všetky lokácie ak sme nenašli správne
if ($locations304->isEmpty() && $novaBudova->isEmpty()) {
    echo "\n🔍 Zobrazujem všetky lokácie:\n";
    $allLocations = Location::orderBy('name')->get();
    foreach ($allLocations as $location) {
        echo "  - ID: {$location->id}, Názov: {$location->name}";
        if ($location->room_number) {
            echo " (Miestnosť: {$location->room_number})";
        }
        echo "\n";
    }
}

// Hľadáme assety s názvom obsahujúcim "Dell"
echo "\n📦 Hľadám assety obsahujúce 'Dell':\n";
$dellAssets = Asset::where('name', 'like', '%Dell%')->get();

echo "Nájdených Dell assetov: " . $dellAssets->count() . "\n";
foreach ($dellAssets as $asset) {
    echo "  - ID: {$asset->id}, Názov: {$asset->name}\n";
    echo "    Aktuálna lokácia: " . ($asset->location ? $asset->location->name : 'Žiadna') . "\n";
}

echo "\n=== KONIEC HĽADANIA ===\n";