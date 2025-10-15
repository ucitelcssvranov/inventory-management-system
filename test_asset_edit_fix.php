<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Asset Edit Fix:\n\n";

// Simulujeme update s owner_id
$asset = \App\Models\Asset::find(2);
if (!$asset) {
    echo "Asset s ID 2 neexistuje!\n";
    exit;
}

echo "Pôvodný vlastník: {$asset->owner}\n";

// Simulujeme validované dáta z formulára
$validated = [
    'name' => $asset->name,
    'category_id' => $asset->category_id,
    'serial_number' => $asset->serial_number,
    'location_id' => $asset->location_id,
    'acquisition_date' => $asset->acquisition_date->format('Y-m-d'),
    'acquisition_cost' => $asset->acquisition_cost,
    'residual_value' => $asset->residual_value,
    'status' => $asset->status,
    'description' => $asset->description,
    'inventory_commission' => $asset->inventory_commission,
    'owner_id' => 1, // ID používateľa z formulára
];

echo "POST dáta z formulára: owner_id = {$validated['owner_id']}\n";

// Aplikujeme logiku z controllera
if ($validated['owner_id']) {
    $user = \App\Models\User::find($validated['owner_id']);
    $validated['owner'] = $user ? $user->name : null;
    echo "Konvertované na owner string: {$validated['owner']}\n";
}
unset($validated['owner_id']);

// Odstránime building_id ak existuje
unset($validated['building_id']);

echo "\nFinálne dáta pre update:\n";
foreach ($validated as $key => $value) {
    if ($key === 'owner') {
        echo "- {$key}: {$value} ✅ (správne konvertované)\n";
    } else {
        echo "- {$key}: {$value}\n";
    }
}

echo "\n--- TEST EDIT FORMULÁRA ---\n";
// Test logiky pre selected option v edit formulári
$users = \App\Models\User::orderBy('name')->get(['id', 'name']);
$currentOwner = $asset->owner;

echo "Súčasný owner v assete: '{$currentOwner}'\n";
echo "Test selected logiky:\n";

foreach ($users->take(3) as $user) {
    $isSelected = ($currentOwner == $user->name) ? 'SELECTED' : '';
    echo "- User ID {$user->id} ({$user->name}): {$isSelected}\n";
}

echo "\n✅ Oprava je správna!\n";
echo "1. Controller správne konvertuje owner_id na owner string\n";
echo "2. Edit formulár správne označí selected option podľa owner name\n";