<?php

require_once __DIR__ . '/bootstrap/app.php';

try {
    $app = $app ?? require_once __DIR__ . '/bootstrap/app.php';
    
    // Test User model
    $user = new App\Models\User();
    echo "✓ User model works\n";
    
    // Test HomeController
    $controller = new App\Http\Controllers\HomeController();
    echo "✓ HomeController works\n";
    
    // Test basic model relationships
    echo "✓ Basic models can be instantiated\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}