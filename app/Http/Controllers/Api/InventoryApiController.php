<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryPlan;
use App\Models\InventoryCommission;
use App\Models\InventoryCount;
use App\Models\InventoryPlanItem;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class InventoryApiController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Zobrazí zoznam inventárnych plánov
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPlans(Request $request): JsonResponse
    {
        try {
            $query = InventoryPlan::with(['commission:id,name']);
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            $plans = $query->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $plans
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní plánov',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Zobrazí detail inventárneho plánu
     *
     * @param InventoryPlan $plan
     * @return JsonResponse
     */
    public function getPlan(InventoryPlan $plan): JsonResponse
    {
        try {
            $plan->load([
                'commission' => function($query) {
                    $query->with(['chairman:id,name', 'members:id,name']);
                }
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $plan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Plán nenájdený',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 404);
        }
    }

    /**
     * Zobrazí inventárne komisie pre používateľa
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserCommissions(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $userId = $request->user_id;
            
            // Nájdi komisie kde je používateľ predseda alebo člen
            $commissions = InventoryCommission::with(['inventoryPlans:id,name,date_start,date_end,status,commission_id'])
                ->where(function($query) use ($userId) {
                    $query->where('chairman_id', $userId)
                          ->orWhereHas('members', function($q) use ($userId) {
                              $q->where('user_id', $userId);
                          });
                })
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $commissions
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validačné chyby',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní komisií',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Zobrazí inventárne počty pre komisiu
     *
     * @param InventoryCommission $commission
     * @return JsonResponse
     */
    public function getInventoryCounts(InventoryCommission $commission): JsonResponse
    {
        try {
            // Získame inventarizačné počty cez plan items pridelené tejto komisii
            $counts = InventoryCount::with(['asset:id,name,inventory_number,category_id,location_id'])
                ->whereHas('planItem', function($query) use ($commission) {
                    $query->where('commission_id', $commission->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $counts,
                'commission' => [
                    'id' => $commission->id,
                    'name' => $commission->name,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní inventúrnych počtov',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Vytvorí alebo aktualizuje inventárny počet
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function recordCount(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'plan_item_id' => 'required|exists:inventory_plan_items,id',
                'asset_id' => 'required|exists:assets,id',
                'counted_quantity' => 'required|integer|min:0',
                'condition' => 'nullable|in:new,good,fair,poor,damaged',
                'notes' => 'nullable|string|max:1000',
                'location_found' => 'nullable|exists:locations,id',
                'photo' => 'nullable|string' // Base64 encoded photo
            ]);

            // Skontroluj či už existuje záznam
            $existingCount = InventoryCount::where('plan_item_id', $validated['plan_item_id'])
                ->where('asset_id', $validated['asset_id'])
                ->first();

            if ($existingCount) {
                $existingCount->update($validated);
                $count = $existingCount;
                $message = 'Inventárny počet bol aktualizovaný';
            } else {
                $count = InventoryCount::create($validated);
                $message = 'Inventárny počet bol zaznamenaný';
            }

            $count->load(['asset:id,name,inventory_number']);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $count
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validačné chyby',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri zapisovaní inventárneho počtu',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Získa prehľad inventúry pre komisiu
     *
     * @param InventoryCommission $commission
     * @return JsonResponse
     */
    public function getInventoryOverview(InventoryCommission $commission): JsonResponse
    {
        try {
            // Celkový počet assets priradených komisii
            $totalAssets = $this->getTotalAssetsForCommission($commission);
            
            // Počet už spočítaných assets
            $countedAssets = InventoryCount::whereHas('planItem', function($query) use ($commission) {
                $query->where('commission_id', $commission->id);
            })->count();
            
            // Počet rozdielov
            $differences = InventoryCount::whereHas('planItem', function($query) use ($commission) {
                $query->where('commission_id', $commission->id);
            })->whereColumn('counted_quantity', '!=', 'expected_quantity')->count();
            
            // Progress
            $progress = $totalAssets > 0 ? round(($countedAssets / $totalAssets) * 100, 1) : 0;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'commission_id' => $commission->id,
                    'commission_name' => $commission->name,
                    'total_assets' => $totalAssets,
                    'counted_assets' => $countedAssets,
                    'remaining_assets' => $totalAssets - $countedAssets,
                    'differences_found' => $differences,
                    'progress_percent' => $progress,
                    'is_completed' => $progress >= 100
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní prehľadu inventúry',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Získa štatistiky pre dashboard
     *
     * @return JsonResponse
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            $stats = $this->cacheService->getDashboardStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní štatistík',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Pomocná funkcia na získanie celkového počtu assets pre komisiu
     */
    private function getTotalAssetsForCommission(InventoryCommission $commission): int
    {
        // Počet všetkých inventory plan items priradených tejto komisii
        return InventoryPlanItem::where('commission_id', $commission->id)->count();
    }
}