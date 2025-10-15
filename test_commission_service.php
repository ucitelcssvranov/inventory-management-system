<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test vytvorenia inventory plánu
use App\Models\User;
use App\Models\InventoryPlan;
use App\Models\Location;
use App\Models\Category;
use App\Services\CommissionAutoAssignmentService;

try {
    // Najdeme admin používateľa
    $user = User::where('email', 'admin@test.com')->first();
    if (!$user) {
        echo "Admin user neexistuje!\n";
        exit(1);
    }
    
    echo "Testujeme službu CommissionAutoAssignmentService...\n";
    
    // Vytvoríme testovací inventory plán
    $plan = new InventoryPlan([
        'name' => 'Test plán',
        'description' => 'Test popis',
        'type' => 'full',
        'status' => 'planned',
        'created_by' => $user->id
    ]);
    
    // Nevkladáme do databázy, len testujeme službu
    $autoAssignmentService = app(CommissionAutoAssignmentService::class);
    
    echo "Vykonávame auto-assignment...\n";
    $assignedCommission = $autoAssignmentService->assignCommission($plan);
    
    if ($assignedCommission) {
        echo "Úspešne priradená komisia: " . $assignedCommission->name . "\n";
    } else {
        echo "Žiadna komisia nebola priradená.\n";
    }
    
    echo "Test dokončený úspešne!\n";
    
} catch (Exception $e) {
    echo "Chyba pri teste: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}