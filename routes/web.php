<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\InventoryPlanController;
use App\Http\Controllers\InventoryCountController;
use App\Http\Controllers\InventoryDifferenceController;
use App\Http\Controllers\InventoryExportController;
use App\Http\Controllers\InventoryCommissionController;
use App\Http\Controllers\InventoryDigitalController;
use App\Http\Controllers\AjaxController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Hlavná stránka - presmeruj na /home, ak nie je prihlásený, presmeruj na login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});

// Test route pre simuláciu používateľa
Route::get('/test-user/{userId}', function ($userId) {
    $user = \App\Models\User::find($userId);
    if ($user) {
        auth()->login($user);
        return redirect()->route('home');
    }
    return 'User not found';
})->middleware('web');

// Auth routes
Auth::routes();

// Microsoft OAuth routes
Route::get('/login/microsoft', [App\Http\Controllers\SocialLoginController::class, 'redirectToMicrosoft'])
    ->name('login.microsoft');
Route::get('/login/microsoft/callback', [App\Http\Controllers\SocialLoginController::class, 'handleMicrosoftCallback'])
    ->name('login.microsoft.callback');

// Home route, only for authenticated users
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->middleware('auth')->name('home');

// Example: Route for správca only
Route::get('/admin', function () {
    return view('admin');
})->middleware(['auth', 'role:spravca']);

// Example: Route for učiteľ only
Route::get('/teacher', function () {
    return view('teacher');
})->middleware(['auth', 'role:ucitel']);

Route::middleware(['auth'])->group(function () {
    Route::resource('assets', AssetController::class);
    
    // AJAX route pre generovanie inventárneho čísla
    Route::post('assets/generate-inventory-number', [AssetController::class, 'generateInventoryNumber'])->name('assets.generate-inventory-number');
    
    Route::resource('categories', CategoryController::class);
    Route::resource('locations', LocationController::class);
    
    // AJAX route pre rýchlu editáciu lokácií
    Route::patch('locations/{location}/quick-update', [LocationController::class, 'quickUpdate'])->name('locations.quick-update');
    Route::resource('inventory_plans', InventoryPlanController::class);

    // Export inventúrnych súpisov/zápisov
    Route::get('inventory_plans/{inventory_plan}/export/soupis', [InventoryExportController::class, 'soupis'])->name('inventory_plans.export.soupis');
    Route::get('inventory_plans/{inventory_plan}/export/zapis', [InventoryExportController::class, 'zapis'])->name('inventory_plans.export.zapis');
    Route::get('inventory_plans/{inventory_plan}/export/soupis/pdf', [InventoryPlanController::class, 'exportSoupisPdf'])->name('inventory_plans.export.soupis.pdf');
    Route::get('inventory_plans/{inventory_plan}/export/soupis/xlsx', [InventoryPlanController::class, 'exportSoupisXlsx'])->name('inventory_plans.export.soupis.xlsx');
    Route::get('inventory_plans/{inventory_plan}/export/zapis/pdf', [InventoryPlanController::class, 'exportZapisPdf'])->name('inventory_plans.export.zapis.pdf');
    Route::get('inventory_plans/{inventory_plan}/export/zapis/xlsx', [InventoryPlanController::class, 'exportZapisXlsx'])->name('inventory_plans.export.zapis.xlsx');

    // Komisia inventarizácie
    Route::get('inventory_plans/{inventory_plan}/commission', [InventoryCommissionController::class, 'show'])->name('inventory_plans.commission.show');
    Route::post('inventory_plans/{inventory_plan}/commission', [InventoryCommissionController::class, 'store'])->name('inventory_plans.commission.store');
    Route::delete('inventory_plans/{inventory_plan}/commission/{user}', [InventoryCommissionController::class, 'destroy'])->name('inventory_plans.commission.destroy');

    // Inventarizačné komisie routy
    Route::resource('inventory-commissions', InventoryCommissionController::class);
    Route::patch('inventory-commissions/{inventoryCommission}/quick-update', [InventoryCommissionController::class, 'quickUpdate'])->name('inventory-commissions.quick-update');
    Route::patch('inventory-commissions/{inventoryCommission}/update-members', [InventoryCommissionController::class, 'updateMembers'])->name('inventory-commissions.update-members');
    Route::post('inventory-commissions/{inventoryCommission}/assign-teachers', [InventoryCommissionController::class, 'assignTeachers'])->name('inventory-commissions.assignTeachers');

    // Inventarizačné plány - komisie a workflow
    Route::get('inventory-plans/commission-dashboard', [InventoryPlanController::class, 'commissionDashboard'])->name('inventory-plans.commission-dashboard');
    Route::get('inventory-plans/{inventoryPlan}/assign-commission', [InventoryPlanController::class, 'assignCommission'])->name('inventory-plans.assign-commission');
    Route::post('inventory-plans/{inventoryPlan}/assign-commission', [InventoryPlanController::class, 'storeCommissionAssignment'])->name('inventory-plans.store-commission-assignment');
    Route::delete('inventory-plans/{inventoryPlan}/remove-commission', [InventoryPlanController::class, 'removeCommissionAssignment'])->name('inventory-plans.remove-commission');
    Route::patch('inventory-plans/{inventoryPlan}/start', [InventoryPlanController::class, 'startInventory'])->name('inventory-plans.start');
    Route::patch('inventory-plans/{inventoryPlan}/complete', [InventoryPlanController::class, 'completeInventory'])->name('inventory-plans.complete');
    Route::patch('inventory-plans/{inventoryPlan}/sign', [InventoryPlanController::class, 'signInventory'])->name('inventory-plans.sign');

    // Akcie nad jednotlivými položkami
    Route::post('inventory-plan-items/{planItem}/start', [App\Http\Controllers\InventoryDigitalController::class, 'startItemInventory'])->name('plan-items.start');
    Route::post('inventory-plan-items/{planItem}/count', [App\Http\Controllers\InventoryDigitalController::class, 'saveItemCount'])->name('plan-items.count');
    Route::post('inventory-plan-items/{planItem}/complete', [App\Http\Controllers\InventoryDigitalController::class, 'completeItemInventory'])->name('plan-items.complete');

    // Dashboard pre komisie
    Route::get('commissions/dashboard', [App\Http\Controllers\InventoryCommissionController::class, 'dashboard'])->name('inventory-commissions.dashboard');
    Route::get('commissions/{inventoryCommission}/dashboard', [App\Http\Controllers\InventoryCommissionController::class, 'commissionDashboard'])->name('inventory-commissions.commission-dashboard');

    // Profil
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    // Správy z inventarizácie
    Route::get('/inventory-reports', [App\Http\Controllers\InventoryReportController::class, 'index'])->name('inventory_reports.index');
    
    // AJAX routes pre dynamické načítavanie dát
    Route::prefix('ajax')->name('ajax.')->group(function () {
        Route::get('/locations/{categoryId}', [AjaxController::class, 'getLocationsByCategory'])->name('locations.by_category');
        Route::get('/buildings', [AjaxController::class, 'getBuildings'])->name('buildings');
        Route::get('/rooms/{buildingId}', [AjaxController::class, 'getRoomsByBuilding'])->name('rooms.by_building');
        Route::get('/assets/{locationId}', [AjaxController::class, 'getAssetsByLocation'])->name('assets.by_location');
        Route::get('/users/{commissionId}', [AjaxController::class, 'getUsersByCommission'])->name('users.by_commission');
        Route::get('/users/search', [AjaxController::class, 'searchUsers'])->name('users.search');
        Route::get('/categories', [AjaxController::class, 'getCategories'])->name('categories');
        Route::get('/locations', [AjaxController::class, 'getLocations'])->name('locations');
        Route::get('/dashboard/stats', [AjaxController::class, 'getDashboardStats'])->name('dashboard.stats');
        
        // Cache management routes
        Route::post('/cache/clear', [AjaxController::class, 'clearCache'])->name('cache.clear');
        Route::post('/cache/warmup', [AjaxController::class, 'warmUpCache'])->name('cache.warmup');
        Route::get('/cache/info', [AjaxController::class, 'getCacheInfo'])->name('cache.info');
    });
});

// Serve static assets if missing (development only)
Route::get('/js/{file}', function ($file) {
    $path = public_path("js/$file");
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
});
Route::get('/css/{file}', function ($file) {
    $path = public_path("css/$file");
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
});
