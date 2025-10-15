<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use App\Models\User;

$user = User::where('email', 'admin@test.com')->first();

if (!$user) {
    $user = User::create([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => bcrypt('admin123'),
        'role' => 'spravca'
    ]);
    echo "Admin user created: " . $user->email . "\n";
} else {
    echo "Admin user already exists: " . $user->email . "\n";
}