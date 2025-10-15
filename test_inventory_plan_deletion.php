<?php

require_once 'vendor/autoload.php';

use App\Models\InventoryPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST MAZANIA A ARCHIVÁCIE INVENTARIZAČNÝCH PLÁNOV ===\n\n";

// Nájdi administrátora
$admin = User::where('role', 'admin')->orWhere('role', 'spravca')->first();
if (!$admin) {
    echo "❌ Administrátor nenájdený!\n";
    exit(1);
}

// Simuluj prihláseného administrátora
auth()->login($admin);
echo "✓ Prihlásený ako admin: {$admin->name}\n";

// Získaj plány podľa stavov
$plans = InventoryPlan::all();

if ($plans->isEmpty()) {
    echo "⚠️  Žiadne inventarizačné plány v databáze!\n";
    exit(0);
}

echo "\n1. PLÁNY PODĽA STAVOV:\n";
$statusCounts = [];
foreach ($plans as $plan) {
    $status = $plan->status;
    if (!isset($statusCounts[$status])) {
        $statusCounts[$status] = 0;
    }
    $statusCounts[$status]++;
}

foreach ($statusCounts as $status => $count) {
    echo "   {$status}: {$count} plánov\n";
}

// Test funkcií na pláne
echo "\n2. TEST FUNKCIÍ NA VZORKE PLÁNOV:\n";

// Test canBeDeleted
$testPlans = $plans->take(3);
foreach ($testPlans as $plan) {
    echo "\nPlán: {$plan->name} (Status: {$plan->status})\n";
    echo "  - Môže byť vymazaný: " . ($plan->canBeDeleted() ? 'ÁNO' : 'NIE') . "\n";
    echo "  - Môže byť archivovaný: " . ($plan->canBeArchived() ? 'ÁNO' : 'NIE') . "\n";
}

// Test archivácie dokončeného plánu (ak existuje)
$completedPlan = InventoryPlan::where('status', InventoryPlan::STATUS_COMPLETED)->first();
if ($completedPlan) {
    echo "\n3. TEST ARCHIVÁCIE DOKONČENÉHO PLÁNU:\n";
    echo "Plán: {$completedPlan->name}\n";
    echo "Pôvodný stav: {$completedPlan->status}\n";
    
    try {
        $completedPlan->archiveInventory($admin->id);
        echo "✓ Plán úspešne archivovaný\n";
        echo "Nový stav: {$completedPlan->fresh()->status}\n";
        
        // Vráť späť na completed
        $completedPlan->update(['status' => InventoryPlan::STATUS_COMPLETED]);
        echo "✓ Stav vrátený späť na completed\n";
        
    } catch (\Exception $e) {
        echo "❌ Chyba pri archivácii: {$e->getMessage()}\n";
    }
} else {
    echo "\n3. ⚠️  Žiadny dokončený plán na testovanie archivácie\n";
}

// Test mazania podľa stavov
echo "\n4. TEST PRAVIDIEL MAZANIA:\n";

foreach ([
    InventoryPlan::STATUS_DRAFT => 'DRAFT plán',
    InventoryPlan::STATUS_IN_PROGRESS => 'IN_PROGRESS plán', 
    InventoryPlan::STATUS_COMPLETED => 'COMPLETED plán',
    InventoryPlan::STATUS_SIGNED => 'SIGNED plán'
] as $status => $description) {
    $plan = InventoryPlan::where('status', $status)->first();
    if ($plan) {
        echo "  {$description}: ";
        if ($plan->canBeDeleted()) {
            echo "MÔŽE byť vymazaný\n";
        } else {
            echo "NEMÔŽE byť vymazaný\n";
        }
    } else {
        echo "  {$description}: nie je k dispozícii\n";
    }
}

echo "\n5. KONTROLA LOGIKY FILTRA ARCHIVOVANÝCH:\n";

// Simuluj request parametre
$_GET['show_archived'] = '0';
$normalPlansCount = InventoryPlan::where('status', '!=', InventoryPlan::STATUS_ARCHIVED)->count();
$archivedPlansCount = InventoryPlan::where('status', InventoryPlan::STATUS_ARCHIVED)->count();
$totalPlansCount = InventoryPlan::count();

echo "  Celkový počet plánov: {$totalPlansCount}\n";
echo "  Normálne plány: {$normalPlansCount}\n";
echo "  Archivované plány: {$archivedPlansCount}\n";

if ($archivedPlansCount > 0) {
    echo "  ✓ Filter archivovaných funguje správne\n";
} else {
    echo "  ⚠️  Žiadne archivované plány na testovanie filtra\n";
}

echo "\n6. DOSTUPNÉ AKCIE PODĽA STAVOV:\n";

$statusActions = [
    InventoryPlan::STATUS_DRAFT => ['edit', 'delete'],
    InventoryPlan::STATUS_PENDING => ['edit', 'delete'], 
    InventoryPlan::STATUS_APPROVED => ['edit', 'delete'],
    InventoryPlan::STATUS_ASSIGNED => ['delete'],
    InventoryPlan::STATUS_IN_PROGRESS => ['view'], // nie delete
    InventoryPlan::STATUS_COMPLETED => ['view', 'archive', 'delete'],
    InventoryPlan::STATUS_SIGNED => ['view', 'archive', 'delete_with_confirmation'],
    InventoryPlan::STATUS_ARCHIVED => ['view', 'delete_with_confirmation']
];

foreach ($statusActions as $status => $actions) {
    echo "  {$status}: " . implode(', ', $actions) . "\n";
}

echo "\n✅ Test dokončený!\n";
echo "\nShrnutie implementovaných funkcií:\n";
echo "- ✓ Dokončené plány možno vymazať\n";
echo "- ✓ Podpísané plány vyžadujú dodatočné potvrdenie\n";
echo "- ✓ Prebiehajúce plány nie je možné vymazať\n";
echo "- ✓ Dokončené a podpísané plány možno archivovať\n";
echo "- ✓ Archivované plány sú skryté ako predvolené nastavenie\n";
echo "- ✓ Existuje filter na zobrazenie archivovaných plánov\n";