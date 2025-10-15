<?php

require_once 'vendor/autoload.php';

// Initialize Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing latest inventory plans for data completeness...\n\n";

try {
    // Get the latest few plans
    $plans = \App\Models\InventoryPlan::with(['location', 'category', 'responsiblePerson', 'createdBy', 'commission'])
                ->latest()
                ->take(5)
                ->get();
    
    echo "Found " . $plans->count() . " plans:\n\n";
    
    foreach ($plans as $plan) {
        echo "Plan ID {$plan->id}: {$plan->name}\n";
        echo "  - Location: " . ($plan->location ? $plan->location->name : 'NULL') . " (ID: {$plan->location_id})\n";
        echo "  - Category: " . ($plan->category ? $plan->category->name : 'NULL') . " (ID: {$plan->category_id})\n";
        echo "  - Responsible Person: " . ($plan->responsiblePerson ? $plan->responsiblePerson->name : 'NULL') . " (ID: {$plan->responsible_person_id})\n";
        echo "  - Created By: " . ($plan->createdBy ? $plan->createdBy->name : 'NULL') . " (ID: {$plan->created_by})\n";
        echo "  - Commission: " . ($plan->commission ? $plan->commission->name : 'NULL') . " (ID: {$plan->commission_id})\n";
        echo "  - Created At: {$plan->created_at}\n\n";
    }
    
    echo "✅ Test completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}