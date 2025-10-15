<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryPlan;

echo "=== STAVY INVENTÚRNYCH PLÁNOV ===\n\n";

$plans = InventoryPlan::select('id', 'name', 'status')->get();

foreach($plans as $plan) {
    echo "ID: {$plan->id}\n";
    echo "Názov: {$plan->name}\n";
    echo "Status: {$plan->status}\n";
    echo "---\n";
}

echo "\n=== DOSTUPNÉ STATUS KONŠTANTY ===\n";
$reflection = new ReflectionClass(InventoryPlan::class);
$constants = $reflection->getConstants();

foreach ($constants as $name => $value) {
    if (strpos($name, 'STATUS_') === 0) {
        echo "{$name}: {$value}\n";
    }
}

echo "\n=== PODMIENKY PRE ARCHIVAČNÉ TLAČIDLO ===\n";
echo "Tlačidlo sa zobrazuje pre stavy: 'completed', 'signed'\n";
echo "Aktuálne plány:\n";

foreach($plans as $plan) {
    $showArchive = in_array($plan->status, ['completed', 'signed']);
    echo "Plan #{$plan->id}: status '{$plan->status}' -> ";
    echo $showArchive ? "ZOBRAZÍ sa archivačné tlačidlo" : "NEZOBRAZÍ sa archivačné tlačidlo";
    echo "\n";
}