<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing inventory plan creation with new location/category fields...\n\n";

try {
    // Get test data
    $user = \App\Models\User::first();
    $location = \App\Models\Location::first();
    $category = \App\Models\Category::first();
    
    if (!$user || !$location || !$category) {
        echo "❌ Missing test data (user, location, or category)\n";
        exit(1);
    }
    
    echo "✓ Using test data:\n";
    echo "  - User: {$user->name} (ID: {$user->id})\n";
    echo "  - Location: {$location->name} (ID: {$location->id})\n";
    echo "  - Category: {$category->name} (ID: {$category->id})\n\n";
    
    // Simulate form data exactly as it would come from the new form
    $formData = [
        'name' => 'Test Plan with Location/Category - ' . date('Y-m-d H:i:s'),
        'date' => now()->format('Y-m-d'),
        'planned_date' => now()->addDays(7)->format('Y-m-d'),
        'date_start' => now()->format('Y-m-d'),
        'date_end' => now()->addDays(3)->format('Y-m-d'),
        'inventory_day' => now()->addDay()->format('Y-m-d'),
        'type' => 'fyzická',
        'status' => 'planned',
        'created_by' => $user->id,
        'responsible_person_id' => $user->id,
        'location_id' => $location->id,  // Plan's main location
        'category_id' => $category->id,  // Plan's category
        'description' => 'Test plan to verify location and category storage',
        'unit_name' => 'Test Unit',
        'unit_address' => 'Test Address',
    ];
    
    $plan = \App\Models\InventoryPlan::create($formData);
    
    echo "✓ Created plan: {$plan->name} (ID: {$plan->id})\n\n";
    
    // Verify the data was stored correctly
    echo "Stored values:\n";
    echo "- location_id: " . ($plan->location_id ?? 'NULL') . "\n";
    echo "- category_id: " . ($plan->category_id ?? 'NULL') . "\n";
    echo "- responsible_person_id: " . ($plan->responsible_person_id ?? 'NULL') . "\n\n";
    
    // Test relationships
    echo "Relationship results:\n";
    echo "- Location: " . ($plan->location ? $plan->location->name : 'NULL') . "\n";
    echo "- Category: " . ($plan->category ? $plan->category->name : 'NULL') . "\n";
    echo "- Responsible Person: " . ($plan->responsiblePerson ? $plan->responsiblePerson->name : 'NULL') . "\n\n";
    
    echo "✅ Test completed successfully! Plan {$plan->id} should now show location, category, and responsible person.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}