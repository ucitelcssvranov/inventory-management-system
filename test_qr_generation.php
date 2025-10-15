<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 TESTOVANIE QR KÓD GENEROVANIA\n";
echo "=================================\n\n";

// Test generovania QR kódu
$asset = \App\Models\Asset::find(49);
if (!$asset) {
    echo "❌ Asset 49 neexistuje\n";
    exit(1);
}

echo "Asset: {$asset->name}\n";
echo "Inventárne číslo: {$asset->inventory_number}\n\n";

$qrService = app(\App\Services\QrCodeService::class);

echo "Current format: " . \App\Models\SystemSetting::get('qr_code_format') . "\n";
echo "Current base URL: " . \App\Models\SystemSetting::get('qr_code_base_url') . "\n\n";

try {
    $filename = $qrService->generateQrCode($asset);
    echo "✅ QR kód vygenerovaný: {$filename}\n";
    
    $qrUrl = $qrService->getQrCodeUrl($asset);
    echo "URL: {$qrUrl}\n";
    
    // Test existencie súboru
    $storagePath = \App\Models\SystemSetting::get('qr_code_storage_path', 'qr-codes');
    $fullPath = "{$storagePath}/{$filename}";
    
    if (Storage::disk('public')->exists($fullPath)) {
        echo "✅ Súbor existuje v storage\n";
        $size = Storage::disk('public')->size($fullPath);
        echo "Veľkosť súboru: {$size} bytes\n";
    } else {
        echo "❌ Súbor neexistuje v storage: {$fullPath}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Chyba: " . $e->getMessage() . "\n";
}

echo "\n=== TEST DOKONČENÝ ===\n";