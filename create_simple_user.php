<?php
// Jednoduchý skript na vytvorenie testovacieho účtu s rolou user
require_once 'vendor/autoload.php';

// Načítanie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    $email = 'test@user.com';
    $password = 'user123';
    $name = 'Test User Account';
    
    echo "Vytváram testovací účet s rolou user...\n";
    
    $existingUser = User::where('email', $email)->first();
    
    if ($existingUser) {
        echo "Účet {$email} už existuje. Aktualizujem...\n";
        $existingUser->update([
            'name' => $name,
            'password' => Hash::make($password),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        echo "✓ Účet bol aktualizovaný!\n";
    } else {
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        echo "✓ Nový účet bol vytvorený!\n";
    }
    
    echo "\n=== PRIHLASOVACIE ÚDAJE ===\n";
    echo "Email: {$email}\n";
    echo "Heslo: {$password}\n";
    echo "Rola: user\n";
    
} catch (Exception $e) {
    echo "Chyba: " . $e->getMessage() . "\n";
}