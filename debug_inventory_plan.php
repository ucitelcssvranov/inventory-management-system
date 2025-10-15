<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryPlan;
use App\Models\InventoryPlanItem;

// Kontrolujeme inventarizačný plán s ID 9
$planId = 9;
echo "=== KONTROLA INVENTARIZAČNÉHO PLÁNU ID: $planId ===\n\n";

$plan = InventoryPlan::find($planId);

if (!$plan) {
    echo "❌ Inventarizačný plán s ID $planId nebol nájdený!\n";
    exit(1);
}

echo "✅ Plán nájdený: {$plan->name}\n";
echo "📋 Status: {$plan->status}\n";
echo "👥 Commission ID: " . ($plan->commission_id ?? 'NIE JE PRIRADENÝ') . "\n";
echo "📅 Dátum začiatku: " . ($plan->date_start ?? 'Nie je nastavený') . "\n";
echo "📅 Dátum konca: " . ($plan->date_end ?? 'Nie je nastavený') . "\n\n";

// Skontrolujeme počet položiek
$itemsCount = $plan->items()->count();
echo "📦 Počet položiek v pláne: $itemsCount\n\n";

if ($itemsCount == 0) {
    echo "❌ PROBLÉM: Plán nemá žiadne položky!\n";
    echo "   Toto je pravdepodobne príčina, prečo sa v súpise nezobrazujú žiadne assety.\n\n";
    
    // Skontrolujeme, či existujú nejaké InventoryPlanItems v databáze vôbec
    $totalItems = InventoryPlanItem::count();
    echo "🔍 Celkový počet InventoryPlanItems v databáze: $totalItems\n";
    
    if ($totalItems > 0) {
        echo "📋 Prvých 5 InventoryPlanItems v databáze:\n";
        $items = InventoryPlanItem::with(['plan', 'asset'])->limit(5)->get();
        foreach ($items as $item) {
            echo "   - ID: {$item->id}, Plan ID: {$item->inventory_plan_id}, Asset: " . ($item->asset->name ?? 'N/A') . "\n";
        }
    }
    
} else {
    echo "✅ Plán obsahuje položky. Zobraziť podrobnosti? (prvých 10)\n";
    $items = $plan->items()->with(['asset', 'commission'])->limit(10)->get();
    
    foreach ($items as $item) {
        echo "   📦 Asset ID: {$item->asset_id}\n";
        echo "      - Názov: " . ($item->asset->name ?? 'N/A') . "\n";
        echo "      - Inventárne číslo: " . ($item->asset->inventory_number ?? 'N/A') . "\n";
        echo "      - Očakávané množstvo: {$item->expected_qty}\n";
        echo "      - Commission ID: " . ($item->commission_id ?? 'NIE JE PRIRADENÝ') . "\n";
        echo "      - Status: " . ($item->assignment_status ?? 'N/A') . "\n";
        echo "   ---\n";
    }
}

// Skontrolujeme komisiu
if ($plan->commission_id) {
    $commission = $plan->commission;
    if ($commission) {
        echo "\n👥 KOMISIA:\n";
        echo "   - Názov: {$commission->name}\n";
        echo "   - Predseda: " . ($commission->chairman->name ?? 'N/A') . "\n";
        echo "   - Počet členov: " . $commission->members()->count() . "\n";
    }
}

// Skontrolujeme export
echo "\n🔍 KONTROLA EXPORTU:\n";
echo "URL súpisu: https://inv.css-vranov.sk/inventory_plans/$planId/export/soupis/pdf\n";

echo "\n=== KONTROLA DOKONČENÁ ===\n";