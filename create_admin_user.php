<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = \Illuminate\Http\Request::capture();
$kernel->handle($request);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    $admin = User::where('email', 'admin@css-vranov.sk')->first();
    
    if (!$admin) {
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@css-vranov.sk',
            'password' => Hash::make('admin123'),
            'role' => 'admin'
        ]);
        echo "Admin vytvorený: admin@css-vranov.sk / admin123\n";
    } else {
        echo "Admin už existuje: admin@css-vranov.sk\n";
    }

} catch (Exception $e) {
    echo "Chyba: " . $e->getMessage() . "\n";
}