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
use App\Models\InventoryPlanItem;

echo "=== TEST VYTVORENIA PLÁNU S FILTRAMI ===\n\n";

// Nájdeme lokáciu "Stará budova" kde sú assety
$staraBuilding = Location::where('name', 'Stará budova')->first();
$hardwareCategory = Category::where('name', 'Hardware')->first();

if (!$staraBuilding || !$hardwareCategory) {
    echo "❌ Nenašli sa potrebné lokácie/kategórie\n";
    exit(1);
}

echo "📍 Testovacia lokácia: {$staraBuilding->name} (ID: {$staraBuilding->id})\n";
echo "📂 Testovacia kategória: {$hardwareCategory->name} (ID: {$hardwareCategory->id})\n";

// Počet assetov v tejto lokácii a kategórii
$assetsCount = Asset::where('location_id', $staraBuilding->id)
                   ->where('category_id', $hardwareCategory->id)
                   ->count();

echo "📦 Assetov v tejto kombinácii: {$assetsCount}\n\n";

// Test vytvorenia plánu programovo
echo "🚀 Vytváram testovací plán...\n";

$planData = [
    'name' => 'Test Plán - Hardware v Starej budove',
    'description' => 'Automaticky vytvorený test plán',
    'type' => 'fyzická',
    'date' => date('Y-m-d'),
    'date_start' => date('Y-m-d'),
    'date_end' => date('Y-m-d', strtotime('+7 days')),
    'inventory_day' => date('Y-m-d', strtotime('+3 days')),
    'unit_name' => 'Centrum stredného odborného vzdelávania Vranov nad Topľou',
    'unit_address' => 'Komenského 2, 093 01 Vranov nad Topľou',
    'created_by' => 1, // Admin user ID
    'status' => 'planned',
    'category_id' => $hardwareCategory->id
];

$plan = InventoryPlan::create($planData);
echo "✅ Plán vytvorený s ID: {$plan->id}\n";

// Simulujeme vytvorenie položiek ako v kontroléri
echo "📋 Vytváram položky plánu...\n";

$query = Asset::where('location_id', $staraBuilding->id)
             ->where('category_id', $hardwareCategory->id);

// Získame assety ktoré ešte nie sú v žiadnom aktívnom pláne
$existingAssetIds = InventoryPlanItem::whereHas('plan', function($q) {
    $q->whereIn('status', ['planned', 'approved', 'assigned', 'in_progress']);
})->pluck('asset_id')->toArray();

if (!empty($existingAssetIds)) {
    $query->whereNotIn('id', $existingAssetIds);
}

$assets = $query->get();
echo "🔍 Nájdených voľných assetov: {$assets->count()}\n";

// Vytvoríme položky plánu
$planItems = [];
foreach ($assets as $asset) {
    $planItems[] = [
        'inventory_plan_id' => $plan->id,
        'asset_id' => $asset->id,
        'expected_qty' => 1,
        'assignment_status' => 'unassigned',
        'created_at' => now(),
        'updated_at' => now()
    ];
}

if (!empty($planItems)) {
    InventoryPlanItem::insert($planItems);
    echo "✅ Vytvorených položiek: " . count($planItems) . "\n";
} else {
    echo "⚠️ Žiadne položky na vytvorenie\n";
}

// Kontrola výsledku
$finalItemsCount = $plan->items()->count();
echo "\n📊 VÝSLEDOK:\n";
echo "   - Plán ID: {$plan->id}\n";
echo "   - Názov: {$plan->name}\n";
echo "   - Položiek v pláne: {$finalItemsCount}\n";
echo "   - Status: {$plan->status}\n";

if ($finalItemsCount > 0) {
    echo "\n📝 Prvé 3 položky:\n";
    $items = $plan->items()->with('asset')->limit(3)->get();
    foreach ($items as $item) {
        echo "   • {$item->asset->name} (ID: {$item->asset->id})\n";
    }
}

echo "\n🌐 URL plánu: https://inv.css-vranov.sk/inventory_plans/{$plan->id}\n";
echo "📋 URL súpisu: https://inv.css-vranov.sk/inventory_plans/{$plan->id}/export/soupis/pdf\n";

echo "\n✅ TEST DOKONČENÝ\n";