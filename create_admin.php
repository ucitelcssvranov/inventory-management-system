<?php
// Skript na vytvorenie nového admin účtu
require_once 'vendor/autoload.php';

// Načítanie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    echo "Hľadám existujúceho používateľa admin@admin.com...\n";
    
    $existingUser = User::where('email', 'admin@admin.com')->first();
    
    if ($existingUser) {
        echo "Používateľ admin@admin.com už existuje!\n";
        echo "Meno: " . $existingUser->name . "\n";
        echo "Rola: " . $existingUser->role . "\n";
        echo "Resetujem heslo na 'admin123'...\n";
        
        $existingUser->password = Hash::make('admin123');
        $existingUser->save();
        
        echo "✓ Heslo bolo úspešne resetované na 'admin123'\n";
    } else {
        echo "Používateľ admin@admin.com neexistuje. Vytváram nový...\n";
        
        $newUser = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
        
        echo "✓ Nový admin účet bol vytvorený!\n";
        echo "Email: admin@admin.com\n";
        echo "Heslo: admin123\n";
        echo "Rola: admin\n";
    }
    
    echo "\n=== VŠETCI ADMIN POUŽÍVATELIA ===\n";
    $admins = User::where('role', 'admin')->get();
    foreach ($admins as $admin) {
        echo "- " . $admin->name . " (" . $admin->email . ")\n";
    }
    
} catch (Exception $e) {
    echo "Chyba: " . $e->getMessage() . "\n";
}