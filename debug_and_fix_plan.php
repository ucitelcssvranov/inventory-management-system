<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryPlan;
use App\Models\InventoryPlanItem;
use App\Models\Asset;

// Kontrolujeme plán 13 ktorý má položky
$planId = 13;
echo "=== KONTROLA INVENTARIZAČNÉHO PLÁNU ID: $planId ===\n\n";

$plan = InventoryPlan::find($planId);

if (!$plan) {
    echo "❌ Inventarizačný plán s ID $planId nebol nájdený!\n";
    exit(1);
}

echo "✅ Plán nájdený: {$plan->name}\n";
echo "📋 Status: {$plan->status}\n";
echo "👥 Commission ID: " . ($plan->commission_id ?? 'NIE JE PRIRADENÝ') . "\n\n";

// Skontrolujeme počet položiek
$itemsCount = $plan->items()->count();
echo "📦 Počet položiek v pláne: $itemsCount\n\n";

if ($itemsCount > 0) {
    echo "✅ Plán obsahuje položky. Detaily:\n";
    $items = $plan->items()->with(['asset'])->get();
    
    foreach ($items as $item) {
        echo "   📦 Item ID: {$item->id}\n";
        echo "      - Asset ID: {$item->asset_id}\n";
        
        // Skontrolujeme, či asset existuje
        $asset = Asset::find($item->asset_id);
        if ($asset) {
            echo "      - Asset nájdený: {$asset->name}\n";
            echo "      - Inventárne číslo: {$asset->inventory_number}\n";
            echo "      - Kategória: " . ($asset->category->name ?? 'N/A') . "\n";
            echo "      - Lokácia: " . ($asset->location->name ?? 'N/A') . "\n";
        } else {
            echo "      - ❌ Asset s ID {$item->asset_id} NEEXISTUJE!\n";
        }
        
        echo "      - Očakávané množstvo: {$item->expected_qty}\n";
        echo "      - Commission ID: " . ($item->commission_id ?? 'NIE JE PRIRADENÝ') . "\n";
        echo "      - Status: " . ($item->assignment_status ?? 'N/A') . "\n";
        echo "   ---\n";
    }
}

// Teraz skúsime pridať nejaké položky do plánu 9 pre test
echo "\n=== POKUS O PRIDANIE POLOŽIEK DO PLÁNU 9 ===\n";

$plan9 = InventoryPlan::find(9);
if ($plan9) {
    // Nájdeme nejaké assety na pridanie
    $assets = Asset::limit(5)->get();
    echo "🔍 Nájdených assetov na pridanie: " . $assets->count() . "\n";
    
    foreach ($assets as $asset) {
        // Skontrolujeme, či už asset nie je v žiadnom pláne
        $existingItem = InventoryPlanItem::where('asset_id', $asset->id)->first();
        if (!$existingItem) {
            echo "➕ Pridávam asset: {$asset->name} (ID: {$asset->id})\n";
            
            InventoryPlanItem::create([
                'inventory_plan_id' => 9,
                'asset_id' => $asset->id,
                'expected_qty' => 1,
                'commission_id' => $plan9->commission_id,
                'assignment_status' => 'assigned'
            ]);
        } else {
            echo "⚠️ Asset {$asset->name} už je v pláne {$existingItem->inventory_plan_id}\n";
        }
    }
    
    // Skontrolujeme počet po pridaní
    $newCount = $plan9->items()->count();
    echo "✅ Plán 9 má teraz $newCount položiek\n";
}

echo "\n=== KONTROLA DOKONČENÁ ===\n";