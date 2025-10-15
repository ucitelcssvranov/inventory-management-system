<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing status of existing plans with 'planned' status...\n\n";

try {
    // Find plans with invalid 'planned' status
    $plansWithPlannedStatus = \App\Models\InventoryPlan::where('status', 'planned')->get();
    
    echo "Found " . $plansWithPlannedStatus->count() . " plans with 'planned' status:\n";
    
    foreach ($plansWithPlannedStatus as $plan) {
        echo "- Plan ID {$plan->id}: {$plan->name}\n";
        echo "  Old status: '{$plan->status}'\n";
        
        // If plan has commission, set status to 'assigned'
        if ($plan->commission_id) {
            $plan->update(['status' => \App\Models\InventoryPlan::STATUS_ASSIGNED]);
            echo "  New status: '{$plan->status}' (Priradený komisii)\n";
        } else {
            // If no commission, set to draft
            $plan->update(['status' => \App\Models\InventoryPlan::STATUS_DRAFT]);
            echo "  New status: '{$plan->status}' (Návrh)\n";
        }
        echo "\n";
    }
    
    echo "✅ All plans with 'planned' status have been fixed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}