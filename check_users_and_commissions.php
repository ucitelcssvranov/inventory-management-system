<?php

require_once 'vendor/autoload.php';

// Nastavenie Laravel aplikácie
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\InventoryCommission;

echo "=== ZOZNAM POUŽÍVATEĽOV ===\n\n";

$users = User::select('id', 'name', 'email', 'role')->get();

foreach($users as $user) {
    echo sprintf("ID: %-3s | %-25s | %-30s | Role: %s\n", 
        $user->id, 
        $user->name, 
        $user->email,
        $user->role ?? 'user'
    );
}

echo "\n=== KOMISIE A ČLENOVIA ===\n\n";

$commissions = InventoryCommission::with(['chairman', 'members'])->get();

foreach($commissions as $commission) {
    echo "📋 Komisia: {$commission->name} (ID: {$commission->id})\n";
    echo "   Predseda: {$commission->chairman->name} (ID: {$commission->chairman_id})\n";
    echo "   Členovia: ";
    
    if ($commission->members->count() > 0) {
        foreach($commission->members as $member) {
            echo "{$member->name} (ID: {$member->id}), ";
        }
        echo "\n";
    } else {
        echo "žiadni\n";
    }
    echo "\n";
}

echo "=== UKONČENÉ ===\n";