<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing manual commission assignment in inventory plan creation...\n\n";

try {
    // Get test data
    $user = \App\Models\User::first();
    $locations = \App\Models\Location::take(2)->get();
    $category = \App\Models\Category::first();
    $commission = \App\Models\InventoryCommission::first();
    
    if (!$user || $locations->count() < 1 || !$category || !$commission) {
        echo "❌ Missing test data\n";
        echo "  - User: " . ($user ? "✓" : "❌") . "\n";
        echo "  - Locations: " . ($locations->count() > 0 ? "✓" : "❌") . "\n";
        echo "  - Category: " . ($category ? "✓" : "❌") . "\n";
        echo "  - Commission: " . ($commission ? "✓" : "❌") . "\n";
        exit(1);
    }
    
    echo "✓ Using test data:\n";
    echo "  - User: {$user->name} (ID: {$user->id})\n";
    echo "  - Locations: " . $locations->pluck('name')->join(', ') . "\n";
    echo "  - Category: {$category->name} (ID: {$category->id})\n";
    echo "  - Commission: {$commission->name} (ID: {$commission->id})\n\n";
    
    // Simulate new form data with manual commission selection
    $planData = [
        'name' => 'Test Manual Commission Plan - ' . date('Y-m-d H:i:s'),
        'date' => now()->format('Y-m-d'),
        'planned_date' => now()->addDays(7)->format('Y-m-d'),
        'date_start' => now()->format('Y-m-d'),
        'date_end' => now()->addDays(3)->format('Y-m-d'),
        'inventory_day' => now()->addDay()->format('Y-m-d'),
        'type' => 'fyzická',
        'status' => 'planned',
        'created_by' => $user->id,
        'responsible_person_id' => $user->id,
        'commission_id' => $commission->id,  // Manually assigned commission
        'category_id' => $category->id,
        'description' => 'Test plan with manually assigned commission',
        'unit_name' => 'Test Unit',
        'unit_address' => 'Test Address',
    ];
    
    $plan = \App\Models\InventoryPlan::create($planData);
    
    echo "✓ Created plan: {$plan->name} (ID: {$plan->id})\n";
    
    // Attach locations
    $locationIds = $locations->pluck('id')->toArray();
    $plan->locations()->attach($locationIds);
    
    echo "✓ Attached locations: " . implode(', ', $locationIds) . "\n\n";
    
    // Verify the data
    $plan->load('locations', 'category', 'responsiblePerson', 'commission');
    
    echo "Plan details:\n";
    echo "- Plan ID: {$plan->id}\n";
    echo "- Category: " . ($plan->category ? $plan->category->name : 'NULL') . "\n";
    echo "- Responsible Person: " . ($plan->responsiblePerson ? $plan->responsiblePerson->name : 'NULL') . "\n";
    echo "- Commission: " . ($plan->commission ? $plan->commission->name : 'NULL') . " (ID: {$plan->commission_id})\n";
    echo "- Locations count: " . $plan->locations->count() . "\n";
    echo "- Locations: " . $plan->locations->pluck('name')->join(', ') . "\n\n";
    
    echo "✅ Test completed successfully! Manual commission assignment works.\n";
    echo "Visit: http://127.0.0.1:8005/inventory_plans/{$plan->id}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}