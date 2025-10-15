<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryPlan;
use App\Models\InventoryPlanItem;
use App\Models\InventoryCommission;

echo "=== NASTAVENIE KOMISIÍ PRE INVENTORY PLAN ITEMS ===\n\n";

// Získame všetky plány s pridelenými komisiami
$plansWithCommissions = InventoryPlan::whereNotNull('commission_id')
    ->with(['commission', 'items'])
    ->get();

$updatedCount = 0;

foreach ($plansWithCommissions as $plan) {
    echo "📋 Plán: {$plan->name} (ID: {$plan->id})\n";
    echo "   Komisia: {$plan->commission->name} (ID: {$plan->commission_id})\n";
    
    // Aktualizujeme všetky items tohto plánu aby mali priradená komisiu
    $itemsToUpdate = $plan->items()
        ->whereNull('commission_id')
        ->orWhere('commission_id', '!=', $plan->commission_id)
        ->get();
    
    if ($itemsToUpdate->count() > 0) {
        echo "   🔧 Aktualizujem {$itemsToUpdate->count()} položiek...\n";
        
        foreach ($itemsToUpdate as $item) {
            $item->update([
                'commission_id' => $plan->commission_id,
                'assignment_status' => InventoryPlanItem::ASSIGNMENT_ASSIGNED // Nastavíme na priradené
            ]);
            $updatedCount++;
        }
    } else {
        echo "   ✅ Všetky položky už majú správne nastavené komisie\n";
    }
    
    echo "\n";
}

echo "📊 VÝSLEDOK:\n";
echo "   - Spracované plány: {$plansWithCommissions->count()}\n";
echo "   - Aktualizované položky: {$updatedCount}\n";

// Kontrola status inventory plan items
echo "\n=== PREHĽAD STATUSOV INVENTORY PLAN ITEMS ===\n";

$statusCounts = InventoryPlanItem::select('assignment_status', DB::raw('count(*) as count'))
    ->groupBy('assignment_status')
    ->get();

foreach ($statusCounts as $statusCount) {
    echo "   {$statusCount->assignment_status}: {$statusCount->count}\n";
}

echo "\n=== UKONČENÉ ===\n";