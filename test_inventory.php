<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Location;
use App\Models\Asset;
use App\Models\InventoryPlan;
use App\Models\InventoryPlanItem;
use App\Models\InventoryDifference;
use App\Models\InventoryCount;

echo "=== TESTOVANIE INVENTARIZÁCIE ===\n\n";

// 1. Kontrola základných dát
echo "1. ZÁKLADNÉ DÁTA:\n";
echo "- Assets: " . Asset::count() . "\n";
echo "- Categories: " . Category::count() . "\n";
echo "- Locations: " . Location::count() . "\n";
echo "- Inventory Plans: " . InventoryPlan::count() . "\n\n";

// 2. Zobrazenie existujúcich kategórií
echo "2. EXISTUJÚCE KATEGÓRIE:\n";
foreach(Category::all() as $category) {
    echo "- {$category->name} (ID: {$category->id})\n";
}
echo "\n";

// 3. Vytvorenie nových kategórií ak neexistujú
echo "3. VYTVORENIE NOVÝCH KATEGÓRIÍ:\n";
$categories = [
    ['name' => 'Výpočtová technika', 'description' => 'Počítače, notebooky, servery'],
    ['name' => 'Kancelárska technika', 'description' => 'Tlačiarne, skenery, kopírky'],
    ['name' => 'Nábytok', 'description' => 'Stoly, stoličky, skrine']
];

foreach($categories as $categoryData) {
    $existing = Category::where('name', $categoryData['name'])->first();
    if (!$existing) {
        $category = Category::create($categoryData);
        echo "- Vytvorená kategória: {$category->name} (ID: {$category->id})\n";
    } else {
        echo "- Kategória už existuje: {$existing->name} (ID: {$existing->id})\n";
    }
}
echo "\n";

// 4. Zobrazenie prvých 5 lokácií
echo "4. PRVÝCH 5 LOKÁCIÍ:\n";
foreach(Location::take(5)->get() as $location) {
    echo "- {$location->name} (ID: {$location->id})\n";
}
echo "\n";

// 5. Vytvorenie testovacích assetov
echo "5. VYTVORENIE TESTOVACÍCH ASSETOV:\n";
$computersCategory = Category::where('name', 'Výpočtová technika')->first();
$officeCategory = Category::where('name', 'Kancelárska technika')->first();
$furnitureCategory = Category::where('name', 'Nábytok')->first();

$firstLocation = Location::first();

if ($computersCategory && $officeCategory && $furnitureCategory && $firstLocation) {
    $testAssets = [
        [
            'name' => 'Test Notebook Dell',
            'description' => 'Testovací notebook pre inventarizáciu',
            'category_id' => $computersCategory->id,
            'location_id' => $firstLocation->id,
            'serial_number' => 'TEST-NB-001',
            'acquisition_date' => '2024-01-15',
            'acquisition_cost' => 850.00,
            'inventory_number' => 'INV-TEST-001'
        ],
        [
            'name' => 'Test Desktop PC',
            'description' => 'Testovací desktop počítač',
            'category_id' => $computersCategory->id,
            'location_id' => $firstLocation->id,
            'serial_number' => 'TEST-PC-001',
            'acquisition_date' => '2024-02-20',
            'acquisition_cost' => 1200.00,
            'inventory_number' => 'INV-TEST-002'
        ],
        [
            'name' => 'Test Tlačiareň HP',
            'description' => 'Testovacia tlačiareň',
            'category_id' => $officeCategory->id,
            'location_id' => $firstLocation->id,
            'serial_number' => 'TEST-PR-001',
            'acquisition_date' => '2024-03-10',
            'acquisition_cost' => 350.00,
            'inventory_number' => 'INV-TEST-003'
        ],
        [
            'name' => 'Test Kancelársky stôl',
            'description' => 'Testovací kancelársky stôl',
            'category_id' => $furnitureCategory->id,
            'location_id' => $firstLocation->id,
            'serial_number' => 'TEST-TB-001',
            'acquisition_date' => '2024-04-05',
            'acquisition_cost' => 250.00,
            'inventory_number' => 'INV-TEST-004'
        ],
    ];

    foreach($testAssets as $assetData) {
        $existing = Asset::where('serial_number', $assetData['serial_number'])->first();
        if (!$existing) {
            $asset = Asset::create($assetData);
            echo "- Vytvorený asset: {$asset->name} (ID: {$asset->id}, SN: {$asset->serial_number})\n";
        } else {
            echo "- Asset už existuje: {$existing->name} (ID: {$existing->id}, SN: {$existing->serial_number})\n";
        }
    }
} else {
    echo "Chyba: Nie sú k dispozícii potrebné kategórie alebo lokácie\n";
}

echo "\n=== KONIEC TESTOVANIA ===\n";

echo "\n6. VYTVORENIE NOVÉHO INVENTÁRNEHO PLÁNU:\n";

// Vytvoríme nový inventárny plán
$newPlan = InventoryPlan::create([
    'name' => 'Test Inventarizácia 2025',
    'description' => 'Testovacia inventarizácia pre overenie procesu',
    'start_date' => '2025-10-01',
    'end_date' => '2025-10-31',
    'status' => 'active'
]);

echo "- Vytvorený inventárny plán: {$newPlan->name} (ID: {$newPlan->id})\n";

// Pridáme testovacie assety do plánu
$testAssets = Asset::whereIn('serial_number', ['TEST-NB-001', 'TEST-PC-001', 'TEST-PR-001', 'TEST-TB-001'])->get();

foreach($testAssets as $asset) {
    $planItem = InventoryPlanItem::create([
        'inventory_plan_id' => $newPlan->id,
        'asset_id' => $asset->id,
        'expected_qty' => 1,
        'assignment_status' => 'unassigned'
    ]);
    echo "- Pridaný asset do plánu: {$asset->name} (item ID: {$planItem->id})\n";
}

echo "\n7. ZOBRAZENIE PLÁNU:\n";
echo "URL pre zobrazenie plánu: http://127.0.0.1:8000/inventory_plans/{$newPlan->id}\n";
echo "Počet položiek v pláne: " . $newPlan->items()->count() . "\n";

echo "\n8. SIMULÁCIA INVENTARIZÁCIE:\n";
// Získame všetky items v pláne a priradíme im rôzne skutoční počty
$planItems = $newPlan->items()->get();

foreach($planItems as $item) {
    // Simulujme rôzne scenáre
    $countedQty = rand(0, 2); // 0 = chýba, 1 = v poriadku, 2 = prebytok
    $note = '';
    
    switch($countedQty) {
        case 0:
            $note = 'Asset nenájdený na uvedenej lokácii';
            break;
        case 1:
            $note = 'Asset nájdený, stav dobrý';
            break;
        case 2:
            $note = 'Nájdený dodatočný kus assetu';
            break;
    }
    
    // Vytvoríme záznam o počítaní
    $count = InventoryCount::create([
        'inventory_plan_item_id' => $item->id,
        'counted_by' => 1, // user ID 1
        'counted_at' => now(),
        'counted_qty' => $countedQty,
        'note' => $note
    ]);
    
    // Aktualizujeme status položky
    $item->update([
        'assignment_status' => InventoryPlanItem::ASSIGNMENT_COMPLETED
    ]);
    
    echo "- Asset: {$item->asset->name} | Očakávané: {$item->expected_qty} | Spočítané: {$countedQty} | Poznámka: {$note}\n";
}

echo "\n9. ANALÝZA ROZDIELOV:\n";
// Analyzujme rozdiely bez vytvárania záznamov v DB
$differencesFound = 0;
foreach($planItems->fresh() as $item) {
    $latestCount = $item->counts()->latest()->first();
    if ($latestCount && $item->expected_qty != $latestCount->counted_qty) {
        $differenceType = $latestCount->counted_qty > $item->expected_qty ? 'surplus' : 'shortage';
        $differenceValue = $latestCount->counted_qty - $item->expected_qty;
        
        echo "- Rozdiel u {$item->asset->name}: {$differenceType} ({$differenceValue})\n";
        $differencesFound++;
    }
}

if ($differencesFound == 0) {
    echo "- Žiadne rozdiely nenájdené\n";
}

echo "\n10. ŠTATATISTIKY INVENTARIZÁCIE:\n";
$totalItems = $planItems->count();
$completedItems = $planItems->where('assignment_status', InventoryPlanItem::ASSIGNMENT_COMPLETED)->count();

echo "- Celkový počet položiek: {$totalItems}\n";
echo "- Dokončené položky: {$completedItems}\n";
echo "- Počet nájdených rozdielov: {$differencesFound}\n";

// Aktualizujeme stav plánu
$newPlan->update([
    'status' => 'completed',
    'completed_at' => now()
]);

echo "- Plán označený ako dokončený\n";

echo "\n11. TESTOVANIE PDF EXPORTU:\n";
echo "URL pre soupis PDF: http://127.0.0.1:8000/inventory_plans/{$newPlan->id}/export/soupis/pdf\n";
echo "URL pre zápis PDF: http://127.0.0.1:8000/inventory_plans/{$newPlan->id}/export/zapis/pdf\n";
echo "Pre test exportu otvorte tieto URL v prehliadači po prihlásení (http://127.0.0.1:8000/test-user/1)\n";