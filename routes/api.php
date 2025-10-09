<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AssetApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\DataApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Verejné endpointy (bez autentifikácie)
Route::prefix('v1')->group(function () {
    Route::get('/app-info', [DataApiController::class, 'getAppInfo']);
    Route::get('/health', [DataApiController::class, 'healthCheck']);
});

// Chránené API endpointy
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Užívateľské info
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });

    // Assets API
    Route::prefix('assets')->group(function () {
        Route::get('/', [AssetApiController::class, 'index']);
        Route::post('/', [AssetApiController::class, 'store']);
        Route::get('/{asset}', [AssetApiController::class, 'show']);
        Route::put('/{asset}', [AssetApiController::class, 'update']);
        Route::delete('/{asset}', [AssetApiController::class, 'destroy']);
        Route::post('/scan', [AssetApiController::class, 'scanCode']);
    });

    // Inventory API
    Route::prefix('inventory')->group(function () {
        Route::get('/plans', [InventoryApiController::class, 'getPlans']);
        Route::get('/plans/{plan}', [InventoryApiController::class, 'getPlan']);
        Route::get('/user-groups', [InventoryApiController::class, 'getUserGroups']);
        Route::get('/groups/{group}/counts', [InventoryApiController::class, 'getInventoryCounts']);
        Route::post('/counts', [InventoryApiController::class, 'recordCount']);
        Route::get('/groups/{group}/overview', [InventoryApiController::class, 'getInventoryOverview']);
        Route::get('/dashboard/stats', [InventoryApiController::class, 'getDashboardStats']);
    });

    // Data API (master data)
    Route::prefix('data')->group(function () {
        Route::get('/categories', [DataApiController::class, 'getCategories']);
        Route::get('/locations', [DataApiController::class, 'getLocations']);
        Route::get('/locations/category/{categoryId}', [DataApiController::class, 'getLocationsByCategory']);
        Route::get('/users', [DataApiController::class, 'getUsers']);
        Route::get('/commissions', [DataApiController::class, 'getCommissions']);
    });
});

