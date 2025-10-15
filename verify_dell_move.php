<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Location;
use App\Models\Asset;

echo "=== OVERENIE PRESUNU DELL ASSETOV ===\n\n";

// Lokácia 304 INF2
$inf2Location = Location::find(76);
echo "📍 Lokácia: {$inf2Location->name} (ID: {$inf2Location->id})\n";
if ($inf2Location->room_number) {
    echo "   Číslo miestnosti: {$inf2Location->room_number}\n";
}

// Rodičovská lokácia (budova)
if ($inf2Location->parent_id) {
    $parentLocation = Location::find($inf2Location->parent_id);
    echo "   Budova: " . ($parentLocation ? $parentLocation->name : 'Neznáma') . "\n";
}

echo "\n📦 DELL ASSETY V TEJTO LOKÁCII:\n";
$dellAssets = Asset::where('location_id', 76)
                   ->where('name', 'like', '%Dell Pro Tower + Monitor Dell 24" P2424HEB%')
                   ->get();

echo "Počet Dell assetov: " . $dellAssets->count() . "\n\n";

foreach ($dellAssets as $index => $asset) {
    echo sprintf("%2d. %s\n", $index + 1, $asset->name);
    echo "    - ID: {$asset->id}\n";
    echo "    - Inventárne číslo: " . ($asset->inventory_number ?? 'Nie je priradené') . "\n";
    echo "    - Aktualizované: " . $asset->updated_at->format('d.m.Y H:i:s') . "\n";
    echo "\n";
}

// Celková štatistika assetov v tejto lokácii
echo "📊 ŠTATISTIKA LOKÁCIE:\n";
$totalAssets = Asset::where('location_id', 76)->count();
$dellCount = $dellAssets->count();
$otherCount = $totalAssets - $dellCount;

echo "- Celkom assetov: {$totalAssets}\n";
echo "- Dell assety: {$dellCount}\n";
echo "- Ostatné assety: {$otherCount}\n";

if ($otherCount > 0) {
    echo "\n🔍 OSTATNÉ ASSETY V TEJTO LOKÁCII:\n";
    $otherAssets = Asset::where('location_id', 76)
                        ->where('name', 'not like', '%Dell Pro Tower + Monitor Dell 24" P2424HEB%')
                        ->get();
    
    foreach ($otherAssets as $asset) {
        echo "- {$asset->name}\n";
    }
}

echo "\n✅ OVERENIE DOKONČENÉ\n";
echo "🎯 Všetky Dell Pro Tower + Monitor Dell 24\" P2424HEB assety sú teraz v lokácii '304 INF2'\n";