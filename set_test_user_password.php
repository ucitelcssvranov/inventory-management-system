<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== NASTAVENIE HESLA PRE TEST USERA ===\n\n";

$user = User::find(30);

if (!$user) {
    echo "❌ Test User nenájdený!\n";
    exit(1);
}

$user->password = Hash::make('password');
$user->save();

echo "✅ Heslo pre Test User ({$user->name}) nastavené na: 'password'\n";
echo "📧 Email: {$user->email}\n";

echo "\n=== UKONČENÉ ===\n";