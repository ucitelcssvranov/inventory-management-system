<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Initialize Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing inventory plan creation with responsible person...\n\n";

try {
    // Get a test user to use as responsible person
    $user = \App\Models\User::first();
    if (!$user) {
        echo "❌ No users found. Create a user first.\n";
        exit(1);
    }
    
    echo "✓ Found user: {$user->name} (ID: {$user->id})\n";
    
    // Get test location and category
    $location = \App\Models\Location::first();
    $category = \App\Models\Category::first();
    
    echo "✓ Found location: " . ($location ? $location->name : 'None') . "\n";
    echo "✓ Found category: " . ($category ? $category->name : 'None') . "\n\n";
    
    // Create test plan with responsible person
    $planData = [
        'name' => 'Test Plan with Responsible Person - ' . date('Y-m-d H:i:s'),
        'date' => now()->format('Y-m-d'),
        'planned_date' => now()->addDays(7)->format('Y-m-d'),
        'type' => 'fyzická',
        'status' => 'planned',
        'created_by' => $user->id,
        'responsible_person_id' => $user->id,
        'location_id' => $location ? $location->id : null,
        'category_id' => $category ? $category->id : null,
        'description' => 'Test plan to verify responsible person functionality',
        'unit_name' => 'Test Unit',
        'unit_address' => 'Test Address',
    ];
    
    $plan = \App\Models\InventoryPlan::create($planData);
    
    echo "✓ Created plan: {$plan->name} (ID: {$plan->id})\n\n";
    
    // Test the relationships
    echo "Testing relationships:\n";
    echo "- Responsible Person: " . ($plan->responsiblePerson ? $plan->responsiblePerson->name : 'NULL') . "\n";
    echo "- Location: " . ($plan->location ? $plan->location->name : 'NULL') . "\n"; 
    echo "- Category: " . ($plan->category ? $plan->category->name : 'NULL') . "\n";
    echo "- Created By: " . ($plan->creator ? $plan->creator->name : 'NULL') . "\n\n";
    
    // Test the database values directly
    echo "Database values:\n";
    $dbPlan = DB::table('inventory_plans')->where('id', $plan->id)->first();
    echo "- responsible_person_id: " . $dbPlan->responsible_person_id . "\n";
    echo "- location_id: " . $dbPlan->location_id . "\n";
    echo "- category_id: " . $dbPlan->category_id . "\n";
    echo "- created_by: " . $dbPlan->created_by . "\n\n";
    
    echo "✅ Test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}