<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;

class DataApiController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Získa všetky kategórie
     *
     * @return JsonResponse
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = $this->cacheService->getCategories();
            
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní kategórií',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Získa všetky lokácie
     *
     * @return JsonResponse
     */
    public function getLocations(): JsonResponse
    {
        try {
            $locations = $this->cacheService->getLocations();
            
            return response()->json([
                'success' => true,
                'data' => $locations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní lokácií',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Získa lokácie pre danú kategóriu
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getLocationsByCategory(int $categoryId): JsonResponse
    {
        try {
            $locations = $this->cacheService->getLocationsByCategory($categoryId);
            
            return response()->json([
                'success' => true,
                'data' => $locations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní lokácií pre kategóriu',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Získa všetkých používateľov
     *
     * @return JsonResponse
     */
    public function getUsers(): JsonResponse
    {
        try {
            $users = $this->cacheService->getUsers();
            
            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní používateľov',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Získa všetky komisie
     *
     * @return JsonResponse
     */
    public function getCommissions(): JsonResponse
    {
        try {
            $commissions = $this->cacheService->getCommissions();
            
            return response()->json([
                'success' => true,
                'data' => $commissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní komisií',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Získa aplikačné informácie a verziu
     *
     * @return JsonResponse
     */
    public function getAppInfo(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'app_name' => config('app.name'),
                'version' => '1.0.0',
                'api_version' => 'v1',
                'environment' => config('app.env'),
                'maintenance_mode' => app()->isDownForMaintenance(),
                'features' => [
                    'asset_management' => true,
                    'inventory_counting' => true,
                    'qr_scanning' => true,
                    'offline_mode' => true,
                    'photo_upload' => true,
                    'real_time_sync' => true
                ],
                'server_time' => now()->toISOString(),
                'timezone' => config('app.timezone')
            ]
        ]);
    }

    /**
     * Health check endpoint
     *
     * @return JsonResponse
     */
    public function healthCheck(): JsonResponse
    {
        try {
            // Základné kontroly
            $checks = [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage()
            ];

            $allHealthy = !in_array(false, $checks);

            return response()->json([
                'success' => $allHealthy,
                'status' => $allHealthy ? 'healthy' : 'unhealthy',
                'checks' => $checks,
                'timestamp' => now()->toISOString()
            ], $allHealthy ? 200 : 503);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Health check failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Kontrola databázy
     */
    private function checkDatabase(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Kontrola cache
     */
    private function checkCache(): bool
    {
        try {
            \Cache::put('health_check', 'ok', 1);
            return \Cache::get('health_check') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Kontrola storage
     */
    private function checkStorage(): bool
    {
        try {
            return is_writable(storage_path());
        } catch (\Exception $e) {
            return false;
        }
    }
}