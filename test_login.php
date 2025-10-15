<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== PRIHLÁSENIE TEST USERA ===\n\n";

// Nájdeme Test User
$user = User::find(30);

if (!$user) {
    echo "❌ Test User nenájdený!\n";
    exit(1);
}

echo "👤 Používateľ: {$user->name} ({$user->email})\n";

// Simulujeme prihlásenie
Auth::login($user);

if (Auth::check()) {
    echo "✅ Používateľ úspešne prihlásený!\n";
    echo "   ID: " . Auth::id() . "\n";
    echo "   Meno: " . Auth::user()->name . "\n";
} else {
    echo "❌ Prihlásenie zlyhalo!\n";
}

echo "\n=== UKONČENÉ ===\n";