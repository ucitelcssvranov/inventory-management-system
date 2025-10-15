<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventoryPlan;

$plan = InventoryPlan::latest()->first();

echo "=== POSLEDNÝ INVENTARIZAČNÝ PLÁN ===\n";
echo "ID: " . $plan->id . "\n";
echo "Názov: " . $plan->name . "\n";
echo "Typ: " . $plan->type . "\n";
echo "Status: " . $plan->status . "\n";
echo "Lokácia ID: " . ($plan->location_id ?? 'null') . "\n";
echo "Kategória ID: " . ($plan->category_id ?? 'null') . "\n";
echo "Zodpovedná osoba ID: " . ($plan->responsible_person_id ?? 'null') . "\n";
echo "Komisia ID: " . ($plan->commission_id ?? 'null') . "\n";

// Testujeme prístupové metódy
echo "\n=== ACCESSOR METÓDY ===\n";
echo "Type Label: " . $plan->type_label . "\n";
echo "Status Label: " . $plan->status_label . "\n";
echo "Status Color: " . $plan->status_color . "\n";

// Eager load vzťahy
$plan->load(['location', 'category', 'responsiblePerson', 'commission']);

echo "\n=== VZŤAHY ===\n";
echo "Lokácia: " . ($plan->location->name ?? 'null') . "\n";
echo "Kategória: " . ($plan->category->name ?? 'null') . "\n";
echo "Zodpovedná osoba: " . ($plan->responsiblePerson->name ?? 'null') . "\n";
echo "Komisia: " . ($plan->commission->name ?? 'null') . "\n";
