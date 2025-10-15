<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryPlanItem;
use App\Models\User;
use App\Http\Controllers\InventoryTaskController;
use Illuminate\Http\Request;

echo "=== DEBUG INVENTORY TASK RECORD COUNT ===\n\n";

// Nájdeme inventory plan item s ID 206
$item = InventoryPlanItem::find(206);

if (!$item) {
    echo "❌ InventoryPlanItem s ID 206 neexistuje!\n";
    
    // Nájdeme nejaký existujúci item
    $item = InventoryPlanItem::first();
    
    if (!$item) {
        echo "❌ Žiadne InventoryPlanItem sa nenašli!\n";
        exit(1);
    }
    
    echo "✅ Použijem namiesto toho item s ID {$item->id}\n";
}

echo "📋 InventoryPlanItem ID: {$item->id}\n";
echo "   Plan ID: {$item->inventory_plan_id}\n";
echo "   Asset ID: {$item->asset_id}\n";
echo "   Status: {$item->assignment_status}\n";
echo "   Commission ID: {$item->commission_id}\n";

// Načítame asset a plán
$item->load(['asset', 'plan.commission']);

if (!$item->asset) {
    echo "❌ Asset pre item {$item->id} neexistuje!\n";
} else {
    echo "   Asset: {$item->asset->name}\n";
}

if (!$item->plan) {
    echo "❌ Plan pre item {$item->id} neexistuje!\n";
} else {
    echo "   Plan: {$item->plan->name}\n";
    
    if (!$item->plan->commission) {
        echo "❌ Komisia pre plán {$item->plan->id} neexistuje!\n";
    } else {
        echo "   Komisia: {$item->plan->commission->name}\n";
    }
}

// Simulujeme používateľa
$user = User::find(30); // Test User
if (!$user) {
    echo "❌ Test User (ID 30) neexistuje!\n";
    exit(1);
}

echo "👤 Používateľ: {$user->name} (ID: {$user->id})\n";

// Simulujeme Auth::login
Auth::login($user);

echo "✅ Používateľ prihlásený\n";

// Skontrolujeme oprávnenia
$hasAdminPrivileges = $user->hasAdminPrivileges();
$isCommissionMember = $user->isCommissionMember($item->plan->commission_id ?? null);

echo "   Admin privilégia: " . ($hasAdminPrivileges ? 'áno' : 'nie') . "\n";
echo "   Člen komisie: " . ($isCommissionMember ? 'áno' : 'nie') . "\n";

// Skúsime vytvoriť controller a request
try {
    $controller = new InventoryTaskController();
    
    // Vytvoríme fake request
    $request = new Request();
    $request->merge([
        'actual_qty' => 1,
        'notes' => 'Test poznámka',
        'condition' => 'good'
    ]);
    
    echo "\n🔧 Simulujem recordCount request...\n";
    echo "   Actual qty: 1\n";
    echo "   Notes: Test poznámka\n";
    echo "   Condition: good\n";
    
    // Zavoláme metódu
    $response = $controller->recordCount($request, $item);
    
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        $data = $response->getData(true);
        echo "✅ Response úspešný!\n";
        echo "   Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        echo "   Message: " . ($data['message'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Neočakávaný typ response\n";
        var_dump($response);
    }
    
} catch (\Exception $e) {
    echo "❌ CHYBA:\n";
    echo "   Správa: {$e->getMessage()}\n";
    echo "   Súbor: {$e->getFile()}:{$e->getLine()}\n";
    echo "   Stack trace:\n{$e->getTraceAsString()}\n";
}

echo "\n=== UKONČENÉ ===\n";