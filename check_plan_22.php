<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Plan ID 22...\n\n";

try {
    $plan = \App\Models\InventoryPlan::find(22);
    
    if (!$plan) {
        echo "❌ Plan with ID 22 not found\n";
        
        // Show latest plans
        $latest = \App\Models\InventoryPlan::latest()->take(3)->get();
        echo "\nLatest plans:\n";
        foreach ($latest as $p) {
            echo "- ID: {$p->id}, Name: {$p->name}\n";
        }
        exit;
    }
    
    echo "✓ Found plan: {$plan->name}\n\n";
    
    echo "Raw database values:\n";
    echo "- location_id: " . ($plan->location_id ?? 'NULL') . "\n";
    echo "- category_id: " . ($plan->category_id ?? 'NULL') . "\n";
    echo "- responsible_person_id: " . ($plan->responsible_person_id ?? 'NULL') . "\n";
    echo "- commission_id: " . ($plan->commission_id ?? 'NULL') . "\n\n";
    
    // Test relationships
    echo "Relationship results:\n";
    echo "- Location: " . ($plan->location ? $plan->location->name : 'NULL') . "\n";
    echo "- Category: " . ($plan->category ? $plan->category->name : 'NULL') . "\n";
    echo "- Responsible Person: " . ($plan->responsiblePerson ? $plan->responsiblePerson->name : 'NULL') . "\n";
    echo "- Commission: " . ($plan->commission ? $plan->commission->name : 'NULL') . "\n\n";
    
    echo "✅ Check completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}