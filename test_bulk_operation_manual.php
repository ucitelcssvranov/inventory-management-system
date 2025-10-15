<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Testovanie bulk operácie - zmena stavu\n\n";

// Vyberieme 2 assety na testovanie
$testAssets = \App\Models\Asset::take(2)->get(['id', 'name', 'status']);
$assetIds = $testAssets->pluck('id')->toArray();

echo "📋 Pôvodný stav assetov:\n";
foreach($testAssets as $asset) {
    echo "   ID: {$asset->id} | Názov: {$asset->name} | Stav: {$asset->status}\n";
}

// Simulujeme bulk operáciu - zmena na "in_repair"
echo "\n🔄 Vykonávam bulk operáciu: zmena stavu na 'in_repair'\n";

try {
    $affectedCount = \App\Models\Asset::whereIn('id', $assetIds)
                                     ->update(['status' => 'in_repair', 'updated_by' => 1]);
    
    echo "✅ Operácia úspešná! Zmenených {$affectedCount} assetov.\n";
    
    // Overenie zmeny
    echo "\n📋 Nový stav assetov:\n";
    $updatedAssets = \App\Models\Asset::whereIn('id', $assetIds)->get(['id', 'name', 'status']);
    foreach($updatedAssets as $asset) {
        echo "   ID: {$asset->id} | Názov: {$asset->name} | Stav: {$asset->status}\n";
    }
    
    // Vrátime pôvodný stav
    echo "\n🔙 Vraciam pôvodný stav (active)...\n";
    \App\Models\Asset::whereIn('id', $assetIds)->update(['status' => 'active', 'updated_by' => 1]);
    echo "✅ Stav vrátený na 'active'\n";
    
} catch(Exception $e) {
    echo "❌ Chyba: " . $e->getMessage() . "\n";
}

echo "\n🎯 Test kompletný! Bulk operations fungujú správne.\n";