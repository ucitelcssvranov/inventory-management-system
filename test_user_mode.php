<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== TESTOVANIE USER MODE FUNCTIONALITY ===\n\n";

// Nájdi admin používateľa
$admin = User::where('role', 'admin')->first();

if (!$admin) {
    echo "Žiadny admin používateľ nenájdený!\n";
    exit;
}

echo "Testing user: {$admin->name} (Role: {$admin->role})\n\n";

// Test 1: Základné admin metódy
echo "1. Základné admin metódy:\n";
echo "   isAdmin(): " . ($admin->isAdmin() ? 'true' : 'false') . "\n";
echo "   isInUserMode(): " . ($admin->isInUserMode() ? 'true' : 'false') . "\n";
echo "   hasAdminPrivileges(): " . ($admin->hasAdminPrivileges() ? 'true' : 'false') . "\n";
echo "   isInventoryManager(): " . ($admin->isInventoryManager() ? 'true' : 'false') . "\n\n";

// Test 2: Simulácia prepnutia do user mode
echo "2. Simulácia prepnutia do user mode:\n";
session(['admin_user_mode' => true]);

echo "   Po prepnutí do user mode:\n";
echo "   isAdmin(): " . ($admin->isAdmin() ? 'true' : 'false') . "\n";
echo "   isInUserMode(): " . ($admin->isInUserMode() ? 'true' : 'false') . "\n";
echo "   hasAdminPrivileges(): " . ($admin->hasAdminPrivileges() ? 'true' : 'false') . "\n";
echo "   isInventoryManager(): " . ($admin->isInventoryManager() ? 'true' : 'false') . "\n\n";

// Test 3: Návrat do admin mode
echo "3. Návrat do admin mode:\n";
session()->forget('admin_user_mode');

echo "   Po návrate do admin mode:\n";
echo "   isAdmin(): " . ($admin->isAdmin() ? 'true' : 'false') . "\n";
echo "   isInUserMode(): " . ($admin->isInUserMode() ? 'true' : 'false') . "\n";
echo "   hasAdminPrivileges(): " . ($admin->hasAdminPrivileges() ? 'true' : 'false') . "\n";
echo "   isInventoryManager(): " . ($admin->isInventoryManager() ? 'true' : 'false') . "\n\n";

echo "=== TEST ÚSPEŠNE DOKONČENÝ ===\n";