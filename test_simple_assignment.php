<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventoryPlan;
use App\Models\User;
use App\Services\CommissionAutoAssignmentService;

try {
    echo "Testujeme CommissionAutoAssignmentService priamo...\n";
    
    // Najdeme admin používateľa
    $user = User::where('email', 'admin@test.com')->first();
    if (!$user) {
        echo "Admin user neexistuje!\n";
        exit(1);
    }
    
    echo "Používateľ: " . $user->name . "\n";
    
    // Vytvoríme skutočný InventoryPlan v databáze
    $plan = InventoryPlan::create([
        'name' => 'Test Plán ' . date('Y-m-d H:i:s'),
        'description' => 'Test popis',
        'type' => 'fyzická',
        'date' => '2025-10-20',
        'date_start' => '2025-10-20',
        'date_end' => '2025-10-25',
        'inventory_day' => '2025-10-22',
        'unit_name' => 'CSŠ Vranov nad Topľou',
        'unit_address' => 'Testovacia adresa 123',
        'status' => 'planned',
        'created_by' => $user->id
    ]);
    
    echo "Vytvorený plán ID: " . $plan->id . "\n";
    
    // Otestujeme service
    echo "Testujeme CommissionAutoAssignmentService...\n";
    $autoAssignmentService = app(CommissionAutoAssignmentService::class);
    
    echo "Vykonávame assignCommission...\n";
    $assignedCommission = $autoAssignmentService->assignCommission($plan);
    
    if ($assignedCommission) {
        echo "Úspešne priradená komisia: " . $assignedCommission->name . " (ID: " . $assignedCommission->id . ")\n";
        
        // Aktualizujeme plán
        $plan->update(['commission_id' => $assignedCommission->id]);
        echo "Plán aktualizovaný s komisiou.\n";
    } else {
        echo "Žiadna komisia nebola priradená.\n";
    }
    
    echo "Test dokončený úspešne!\n";
    
} catch (Exception $e) {
    echo "Chyba: " . $e->getMessage() . "\n";
    echo "Súbor: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}