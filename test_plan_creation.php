<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryPlan;
use App\Models\Location;
use App\Models\Category;
use App\Models\Asset;

echo "=== TEST NOVÉHO SYSTÉMU VYTVÁRANIA PLÁNOV ===\n\n";

// Zobrazíme dostupné lokácie a kategórie
echo "📍 DOSTUPNÉ LOKÁCIE:\n";
$locations = Location::orderBy('name')->get();
foreach ($locations as $location) {
    $assetsCount = Asset::where('location_id', $location->id)->count();
    echo "  - {$location->name} (ID: {$location->id}) - {$assetsCount} assetov\n";
}

echo "\n📂 DOSTUPNÉ KATEGÓRIE:\n";
$categories = Category::orderBy('name')->get();
foreach ($categories as $category) {
    $assetsCount = Asset::where('category_id', $category->id)->count();
    echo "  - {$category->name} (ID: {$category->id}) - {$assetsCount} assetov\n";
}

// Simulujeme vytvorenie plánu s filtermi
echo "\n🧪 SIMULÁCIA VYTVORENIA PLÁNU:\n";

// Test 1: Plán pre konkrétnu lokáciu
$testLocationId = $locations->first()->id;
echo "\nTest 1: Plán pre lokáciu '{$locations->first()->name}'\n";

$query = Asset::where('location_id', $testLocationId);
$assetsCount = $query->count();
echo "  - Nájdených assetov: {$assetsCount}\n";

if ($assetsCount > 0) {
    $assets = $query->limit(3)->get();
    echo "  - Prvé 3 assety:\n";
    foreach ($assets as $asset) {
        echo "    • {$asset->name} (ID: {$asset->id})\n";
    }
}

// Test 2: Plán pre konkrétnu kategóriu
$testCategoryId = $categories->first()->id;
echo "\nTest 2: Plán pre kategóriu '{$categories->first()->name}'\n";

$query2 = Asset::where('category_id', $testCategoryId);
$assetsCount2 = $query2->count();
echo "  - Nájdených assetov: {$assetsCount2}\n";

// Test 3: Kombinácia lokácie a kategórie
echo "\nTest 3: Kombinácia lokácie a kategórie\n";
$query3 = Asset::where('location_id', $testLocationId)
              ->where('category_id', $testCategoryId);
$assetsCount3 = $query3->count();
echo "  - Nájdených assetov: {$assetsCount3}\n";

echo "\n✅ TESTY DOKONČENÉ\n";
echo "💡 Formulár by mal teraz správne filtrovať assety podľa vybraných lokácií a kategórií.\n";