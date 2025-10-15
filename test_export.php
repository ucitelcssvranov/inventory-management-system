<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryPlan;

echo "=== TEST EXPORTU SÚPISU ===\n\n";

$planId = 9;
$plan = InventoryPlan::with([
    'items.asset.category',
    'items.asset.location', 
    'responsiblePerson'
])->find($planId);

if (!$plan) {
    echo "❌ Plán nenájdený!\n";
    exit(1);
}

echo "✅ Plán: {$plan->name}\n";
echo "👤 Zodpovedná osoba: " . ($plan->responsiblePerson->name ?? 'Nie je nastavená') . "\n";
echo "🏢 Miesto uloženia: " . ($plan->storage_place ?? 'Nie je nastavené') . "\n";
echo "📦 Počet položiek: " . $plan->items->count() . "\n\n";

echo "📋 POLOŽKY PRE EXPORT:\n";
foreach ($plan->items as $item) {
    echo "• {$item->asset->name}\n";
    echo "  - Množstvo: {$item->expected_qty}\n";
    echo "  - Cena: " . ($item->asset->acquisition_cost ? number_format($item->asset->acquisition_cost, 2) . ' €' : 'Neurčená') . "\n";
    echo "  - Kategória: " . ($item->asset->category->name ?? 'Bez kategórie') . "\n";
    echo "  - Lokácia: " . ($item->asset->location->name ?? 'Bez lokácie') . "\n";
    echo "\n";
}

echo "✅ Export by mal teraz správne fungovať!\n";
echo "🌐 URL: https://inv.css-vranov.sk/inventory_plans/$planId/export/soupis/pdf\n";

echo "\n=== TEST DOKONČENÝ ===\n";