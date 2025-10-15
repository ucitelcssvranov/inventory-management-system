<?php
// Skript na vytvorenie testovacieho účtu s rolou user
require_once 'vendor/autoload.php';

// Načítanie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    echo "=== VYTVORENIE TESTOVACIEHO ÚČTU S ROLOU USER ===\n\n";
    
    $testEmail = 'user@test.com';
    $testPassword = 'password123';
    
    echo "Hľadám existujúceho používateľa {$testEmail}...\n";
    
    $existingUser = User::where('email', $testEmail)->first();
    
    if ($existingUser) {
        echo "Používateľ {$testEmail} už existuje!\n";
        echo "Meno: " . $existingUser->name . "\n";
        echo "Rola: " . $existingUser->role . "\n";
        echo "Aktualizujem údaje...\n";
        
        $existingUser->name = 'Test User';
        $existingUser->password = Hash::make($testPassword);
        $existingUser->role = 'user';
        $existingUser->email_verified_at = now();
        $existingUser->save();
        
        echo "✓ Používateľ bol úspešne aktualizovaný!\n";
    } else {
        echo "Používateľ {$testEmail} neexistuje. Vytváram nový...\n";
        
        $newUser = User::create([
            'name' => 'Test User',
            'email' => $testEmail,
            'email_verified_at' => now(),
            'password' => Hash::make($testPassword),
            'role' => 'user',
        ]);
        
        echo "✓ Nový testovací účet bol vytvorený!\n";
    }
    
    echo "\n=== ÚDAJE TESTOVACIEHO ÚČTU ===\n";
    echo "Email: {$testEmail}\n";
    echo "Heslo: {$testPassword}\n";
    echo "Rola: user\n";
    
    // Vytvorenie ďalších testovacích účtov s rôznymi rolami
    echo "\n=== VYTVORENIE ĎALŠÍCH TESTOVACÍCH ÚČTOV ===\n";
    
    $testUsers = [
        [
            'name' => 'Inventory Manager Test',
            'email' => 'inventory@test.com',
            'role' => 'inventory_manager',
            'password' => 'inventory123'
        ],
        [
            'name' => 'Group Leader Test',
            'email' => 'leader@test.com',
            'role' => 'group_leader',
            'password' => 'leader123'
        ],
        [
            'name' => 'Inventorisator Test',
            'email' => 'inventorisator@test.com',
            'role' => 'inventorisator',
            'password' => 'inventorisator123'
        ],
    ];
    
    foreach ($testUsers as $userData) {
        $existingUser = User::where('email', $userData['email'])->first();
        
        if ($existingUser) {
            echo "- Aktualizujem {$userData['email']}...\n";
            $existingUser->name = $userData['name'];
            $existingUser->password = Hash::make($userData['password']);
            $existingUser->role = $userData['role'];
            $existingUser->email_verified_at = now();
            $existingUser->save();
        } else {
            echo "- Vytváram {$userData['email']}...\n";
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($userData['password']),
                'role' => $userData['role'],
            ]);
        }
        
        echo "  ✓ {$userData['name']} ({$userData['role']})\n";
        echo "    Email: {$userData['email']}\n";
        echo "    Heslo: {$userData['password']}\n\n";
    }
    
    echo "\n=== VŠETCI TESTOVACIE POUŽÍVATELIA ===\n";
    $testEmails = ['user@test.com', 'inventory@test.com', 'leader@test.com', 'inventorisator@test.com'];
    $testUsers = User::whereIn('email', $testEmails)->get();
    
    foreach ($testUsers as $user) {
        echo "- " . $user->name . " (" . $user->email . ") - Rola: " . $user->role . "\n";
    }
    
    echo "\n=== SÚHRN VŠETKÝCH POUŽÍVATEĽOV PODĽA ROLÍ ===\n";
    $roles = ['admin', 'user', 'inventory_manager', 'group_leader', 'inventorisator', 'spravca', 'ucitel'];
    
    foreach ($roles as $role) {
        $count = User::where('role', $role)->count();
        if ($count > 0) {
            echo "- {$role}: {$count} používateľov\n";
        }
    }
    
} catch (Exception $e) {
    echo "Chyba: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}