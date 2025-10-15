<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\InventoryPlanController;
use Illuminate\Http\Request;
use App\Models\User;

try {
    echo "Simulujeme vytvorenie inventory plánu...\n";
    
    // Najdeme admin používateľa
    $user = User::where('email', 'admin@test.com')->first();
    if (!$user) {
        echo "Admin user neexistuje!\n";
        exit(1);
    }
    
    // Simulujeme authenticated session
    auth()->login($user);
    
    echo "Používateľ prihlásený: " . auth()->user()->name . "\n";
    
    // Vytvoríme request objekt s fake data
    $requestData = [
        'name' => 'Test Inventory Plán ' . date('Y-m-d H:i:s'),
        'description' => 'Test popis pre inventory plán',
        'type' => 'fyzická',
        'date' => '2025-10-20',
        'date_start' => '2025-10-20',
        'date_end' => '2025-10-25',
        'inventory_day' => '2025-10-22',
        'unit_name' => 'CSŠ Vranov nad Topľou',
        'unit_address' => 'Testovacia adresa 123, Vranov nad Topľou',
        'location_ids' => [1], // Prvá lokácia
        'category_id' => 1      // Prvá kategória
    ];
    
    echo "Data pre vytvorenie plánu:\n";
    print_r($requestData);
    
    // Vytvoríme request objekt
    $request = new Request($requestData);
    
    // Vytvoríme controller instance
    $controller = new InventoryPlanController();
    
    echo "Vykonávame store metódu...\n";
    
    // Vykonáme store metódu
    $response = $controller->store($request);
    
    echo "Response status: " . $response->status() . "\n";
    echo "Response type: " . get_class($response) . "\n";
    
    if ($response->status() == 302) {
        echo "Redirect response - úspešne vytvorené!\n";
    } else {
        echo "Response content:\n";
        echo $response->getContent() . "\n";
    }
    
} catch (Exception $e) {
    echo "Chyba: " . $e->getMessage() . "\n";
    echo "Súbor: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}