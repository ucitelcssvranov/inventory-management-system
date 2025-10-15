<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Location;
use App\Models\Asset;

echo "=== PRESUN DELL ASSETOV DO NOVEJ LOKÁCIE ===\n\n";

// Cieľová lokácia - 304 INF2 (ID: 76)
$targetLocationId = 76; // 304 INF2
$targetLocation = Location::find($targetLocationId);

if (!$targetLocation) {
    echo "❌ Lokácia s ID {$targetLocationId} nebola nájdená!\n";
    exit(1);
}

echo "🎯 Cieľová lokácia: {$targetLocation->name} (ID: {$targetLocation->id})\n\n";

// Nájdeme všetky Dell assety
$dellAssets = Asset::where('name', 'like', '%Dell Pro Tower + Monitor Dell 24" P2424HEB%')->get();

echo "📦 Nájdených Dell assetov na presun: " . $dellAssets->count() . "\n\n";

if ($dellAssets->isEmpty()) {
    echo "⚠️ Žiadne Dell assety na presun!\n";
    exit(0);
}

// Spýtame sa na potvrdenie
echo "🔄 Presunúť všetky Dell assety do lokácie '{$targetLocation->name}'? (y/n): ";
$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmation) !== 'y' && strtolower($confirmation) !== 'yes') {
    echo "❌ Operácia zrušená.\n";
    exit(0);
}

echo "\n🚀 Začínam presun assetov...\n\n";

$successCount = 0;
$errorCount = 0;

foreach ($dellAssets as $asset) {
    try {
        $oldLocation = $asset->location ? $asset->location->name : 'Žiadna';
        
        // Aktualizujeme lokáciu
        $asset->update([
            'location_id' => $targetLocationId,
            'updated_by' => 1, // Admin user ID
            'updated_at' => now()
        ]);
        
        echo "✅ {$asset->name}\n";
        echo "   Presunuré z: {$oldLocation} → {$targetLocation->name}\n";
        
        $successCount++;
        
    } catch (Exception $e) {
        echo "❌ Chyba pri presune {$asset->name}: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 VÝSLEDOK PRESUNU:\n";
echo "✅ Úspešne presunuté: {$successCount}\n";
echo "❌ Chyby: {$errorCount}\n";
echo "📍 Cieľová lokácia: {$targetLocation->name}\n";

if ($successCount > 0) {
    echo "\n🎉 Presun dokončený úspešne!\n";
    
    // Overenie výsledku
    echo "\n🔍 OVERENIE:\n";
    $verifyAssets = Asset::where('name', 'like', '%Dell Pro Tower + Monitor Dell 24" P2424HEB%')
                          ->where('location_id', $targetLocationId)
                          ->count();
    echo "Assetov v cieľovej lokácii: {$verifyAssets}\n";
} else {
    echo "\n⚠️ Žiadne assety neboli presunuté.\n";
}

echo "\n=== PRESUN DOKONČENÝ ===\n";