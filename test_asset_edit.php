<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Asset Edit functionality:\n\n";

// Nájdeme asset s ID 2
$asset = \App\Models\Asset::find(2);
if (!$asset) {
    echo "Asset s ID 2 neexistuje!\n";
    exit;
}

echo "Asset ID: {$asset->id}\n";
echo "Názov: {$asset->name}\n";
echo "Súčasný vlastník: {$asset->owner}\n";
echo "Owner ID stĺpec v databáze: " . ($asset->owner_id ?? 'NULL') . "\n\n";

// Skontrolujme štruktúru databázy
echo "Štruktúra assets tabuľky:\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('assets');
foreach ($columns as $column) {
    echo "- {$column}\n";
}

echo "\n--- PROBLÉM ---\n";
echo "1. Formulár edit.blade.php používa select field 'owner_id' (ID používateľa)\n";
echo "2. Asset controller v update() metóde očakáva 'owner' (string - meno)\n";
echo "3. V databáze sa ukladá 'owner' ako string, nie owner_id\n";
echo "4. Formulár má chybu - odoslanie owner_id nekonvertuje na owner string\n\n";

// Simulujme POST data z formulára
echo "Simulácia POST dát z formulára:\n";
echo "owner_id = 1 (z formulára select)\n";

// Nájdeme používateľa
$user = \App\Models\User::find(1);
if ($user) {
    echo "Používateľ s ID 1: {$user->name}\n";
    echo "Controller by mal konvertovať owner_id na owner string\n";
} else {
    echo "Používateľ s ID 1 neexistuje!\n";
}

echo "\n--- RIEŠENIE ---\n";
echo "V AssetController::update() treba pridať konverziu owner_id na owner:\n";
echo "if (\$validated['owner_id']) {\n";
echo "    \$user = \\App\\Models\\User::find(\$validated['owner_id']);\n";
echo "    \$validated['owner'] = \$user ? \$user->name : null;\n";
echo "}\n";
echo "unset(\$validated['owner_id']);\n";