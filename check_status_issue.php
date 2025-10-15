<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking plan status issue...\n\n";

try {
    $plan = \App\Models\InventoryPlan::latest()->first();
    
    if (!$plan) {
        echo "❌ No plans found!\n";
        return;
    }
    
    echo "Latest plan (ID {$plan->id}) details:\n";
    echo "- Status: '{$plan->status}'\n";
    echo "- Commission ID: {$plan->commission_id}\n\n";
    
    // Check available status constants
    echo "Available status constants:\n";
    $reflection = new ReflectionClass(\App\Models\InventoryPlan::class);
    $constants = $reflection->getConstants();
    
    foreach ($constants as $name => $value) {
        if (strpos($name, 'STATUS_') === 0) {
            echo "- {$name}: '{$value}'\n";
        }
    }
    
    echo "\n";
    
    // Check status label method
    echo "Status label: " . $plan->getStatusLabelAttribute() . "\n";
    echo "Status options: \n";
    $options = \App\Models\InventoryPlan::getStatusOptions();
    foreach ($options as $value => $label) {
        echo "- '{$value}' => '{$label}'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}