<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryPlan;
use App\Models\InventoryPlanItem;
use App\Models\Asset;
use App\Models\InventoryCommission;

/**
 * Funkcia na pridanie assetov do inventarizačného plánu
 */
function addAssetsToInventoryPlan($planId, $assetIds, $commissionId = null) {
    $plan = InventoryPlan::find($planId);
    if (!$plan) {
        echo "❌ Plán s ID $planId neexistuje!\n";
        return false;
    }
    
    $addedCount = 0;
    foreach ($assetIds as $assetId) {
        $asset = Asset::find($assetId);
        if (!$asset) {
            echo "⚠️ Asset s ID $assetId neexistuje, preskakujem...\n";
            continue;
        }
        
        // Skontroluj, či asset už nie je v nejakom pláne
        $existing = InventoryPlanItem::where('asset_id', $assetId)->first();
        if ($existing) {
            echo "⚠️ Asset '{$asset->name}' už je v pláne {$existing->inventory_plan_id}, preskakujem...\n";
            continue;
        }
        
        InventoryPlanItem::create([
            'inventory_plan_id' => $planId,
            'asset_id' => $assetId,
            'expected_qty' => 1,
            'commission_id' => $commissionId ?: $plan->commission_id,
            'assignment_status' => 'assigned'
        ]);
        
        echo "✅ Pridaný asset: {$asset->name}\n";
        $addedCount++;
    }
    
    echo "📦 Celkom pridaných assetov: $addedCount\n";
    return $addedCount;
}

/**
 * Funkcia na kontrolu integrity inventarizačných plánov
 */
function checkInventoryPlanIntegrity() {
    echo "=== KONTROLA INTEGRITY INVENTARIZAČNÝCH PLÁNOV ===\n\n";
    
    $plans = InventoryPlan::with(['items', 'commission'])->get();
    
    foreach ($plans as $plan) {
        echo "📋 Plán: {$plan->name} (ID: {$plan->id})\n";
        echo "   Status: {$plan->status}\n";
        echo "   Komisia: " . ($plan->commission->name ?? 'NEPRIRADENÁ') . "\n";
        
        $itemsCount = $plan->items->count();
        echo "   Položky: $itemsCount\n";
        
        if ($itemsCount == 0) {
            echo "   ❌ PROBLÉM: Plán nemá žiadne položky!\n";
        }
        
        // Skontroluj chýbajúce assety
        $missingAssets = 0;
        foreach ($plan->items as $item) {
            if (!$item->asset) {
                $missingAssets++;
            }
        }
        
        if ($missingAssets > 0) {
            echo "   ❌ PROBLÉM: $missingAssets položiek odkazuje na neexistujúce assety!\n";
        }
        
        echo "   ---\n";
    }
}

/**
 * Funkcia na vyčistenie orphaned items
 */
function cleanupOrphanedItems() {
    echo "=== ČISTENIE ORPHANED ITEMS ===\n\n";
    
    // Nájdi items bez existujúcich assetov
    $orphanedItems = InventoryPlanItem::whereNotExists(function($query) {
        $query->select(\DB::raw(1))
              ->from('assets')
              ->whereRaw('assets.id = inventory_plan_items.asset_id');
    })->get();
    
    echo "🔍 Nájdených orphaned items: " . $orphanedItems->count() . "\n";
    
    foreach ($orphanedItems as $item) {
        echo "🗑️ Odstraňujem item ID: {$item->id} (asset_id: {$item->asset_id})\n";
        $item->delete();
    }
    
    echo "✅ Čistenie dokončené\n";
}

// Spustenie kontrol
echo "🚀 SPÚŠŤAM DIAGNOSTIKU INVENTARIZAČNÝCH PLÁNOV\n\n";

checkInventoryPlanIntegrity();
cleanupOrphanedItems();

echo "\n✅ DIAGNOSTIKA DOKONČENÁ\n";
echo "💡 Pre pridanie assetov do plánu použite funkciu addAssetsToInventoryPlan(\$planId, \$assetIds)\n";