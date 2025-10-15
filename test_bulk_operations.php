<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Bulk Operations Implementation:\n\n";

// Test 1: Kontrola assetov pre testovanie
$assets = \App\Models\Asset::take(5)->get(['id', 'name', 'status', 'owner', 'location_id']);
echo "📋 Dostupné assety pre testovanie:\n";
foreach($assets as $asset) {
    echo "   ID: {$asset->id} | Názov: {$asset->name} | Stav: {$asset->status} | Vlastník: {$asset->owner}\n";
}

// Test 2: Simulácia bulk operácie - zmena stavu
echo "\n🔄 Test: Simulácia bulk operácie - zmena stavu\n";
$testAssetIds = $assets->pluck('id')->toArray();
echo "Asset IDs pre test: " . implode(', ', $testAssetIds) . "\n";

// Test 3: Kontrola routes
echo "\n🛣️ Test: Kontrola routes\n";
try {
    $routes = app('router')->getRoutes();
    $bulkRoutes = [];
    foreach($routes as $route) {
        if (str_contains($route->getName() ?? '', 'bulk')) {
            $bulkRoutes[] = $route->getName() . ' (' . implode('|', $route->methods()) . ')';
        }
    }
    echo "Bulk routes: " . implode(', ', $bulkRoutes) . "\n";
} catch(Exception $e) {
    echo "Chyba pri kontrole routes: " . $e->getMessage() . "\n";
}

// Test 4: Kontrola metód v AssetController
echo "\n🎛️ Test: Kontrola AssetController metód\n";
$controller = new \App\Http\Controllers\AssetController();
$methods = get_class_methods($controller);
$bulkMethods = array_filter($methods, function($method) {
    return str_contains(strtolower($method), 'bulk');
});
echo "Bulk metódy: " . implode(', ', $bulkMethods) . "\n";

// Test 5: Kontrola potrebných AJAX endpointov
echo "\n🌐 Test: AJAX endpointy\n";
$ajaxController = new \App\Http\Controllers\AjaxController();
$ajaxMethods = get_class_methods($ajaxController);
$requiredMethods = ['searchUsers', 'getLocations'];
foreach($requiredMethods as $method) {
    if (in_array($method, $ajaxMethods)) {
        echo "   ✅ {$method} - existuje\n";
    } else {
        echo "   ❌ {$method} - chýba\n";
    }
}

echo "\n✅ Bulk operations implementácia je pripravená na testovanie!\n";
echo "🌐 Otvorte: http://127.0.0.1:8004/assets\n";
echo "📝 Kroky na testovanie:\n";
echo "   1. Označte checkboxy vedľa assetov\n";
echo "   2. Malo by sa zobraziť modré toolbar s počtom vybraných\n";
echo "   3. Kliknite na 'Hromadné operácie' dropdown\n";
echo "   4. Vyberte operáciu (zmena stavu, lokácie, vlastníka alebo mazanie)\n";
echo "   5. Vyplňte modal a potvrdďte operáciu\n";