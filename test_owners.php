<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Assets with owners:\n";
echo "Total count: " . \App\Models\Asset::whereNotNull('owner')->distinct()->count() . "\n\n";

echo "All distinct owners:\n";
$owners = \App\Models\Asset::whereNotNull('owner')->distinct()->pluck('owner')->sort()->values();

foreach($owners as $index => $owner) {
    echo ($index + 1) . ". " . $owner . "\n";
}

echo "\nFirst 10 owners:\n";
$owners->take(10)->each(function($owner, $index) {
    echo ($index + 1) . ". " . $owner . "\n";
});