<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG INFO ===\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "Environment: " . app()->environment() . "\n";
echo "Database: " . config('database.default') . "\n";

try {
    echo "Locations count: " . \App\Models\Location::count() . "\n";
    echo "Buildings count: " . \App\Models\Location::budovy()->count() . "\n";
    
    $buildings = \App\Models\Location::budovy()->with(['createdBy'])->withCount(['children as miestnosti_count', 'assets'])->get();
    echo "Buildings with counts:\n";
    foreach ($buildings as $building) {
        echo "- {$building->name} (ID: {$building->id}, Rooms: {$building->miestnosti_count}, Assets: {$building->assets_count})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== ROUTES INFO ===\n";
$routes = ['locations.index', 'locations.show', 'locations.quick-update', 'locations.destroy'];
foreach ($routes as $routeName) {
    try {
        echo "$routeName: " . route($routeName, ['location' => 1]) . "\n";
    } catch (Exception $e) {
        echo "$routeName: ERROR - " . $e->getMessage() . "\n";
    }
}