<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryPlan;
use App\Models\InventoryGroup;
use App\Models\InventoryCount;
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
            $query = InventoryPlan::with(['groups:id,name,inventory_plan_id']);
            
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
                'groups' => function($query) {
                    $query->with(['leader:id,name', 'members:id,name', 'commission:id,name']);
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
     * Zobrazí inventárne skupiny pre používateľa
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserGroups(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $userId = $request->user_id;
            
            // Nájdi skupiny kde je používateľ vedúci alebo člen
            $groups = InventoryGroup::with(['plan:id,name,start_date,end_date,status', 'commission:id,name'])
                ->where(function($query) use ($userId) {
                    $query->where('leader_id', $userId)
                          ->orWhereHas('members', function($q) use ($userId) {
                              $q->where('user_id', $userId);
                          });
                })
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $groups
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
                'message' => 'Chyba pri načítavaní skupín',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Zobrazí inventárne počty pre skupinu
     *
     * @param InventoryGroup $group
     * @return JsonResponse
     */
    public function getInventoryCounts(InventoryGroup $group): JsonResponse
    {
        try {
            $counts = InventoryCount::with(['asset:id,name,inventory_number,category_id,location_id'])
                ->where('inventory_group_id', $group->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $counts,
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'status' => $group->status
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
                'inventory_group_id' => 'required|exists:inventory_groups,id',
                'asset_id' => 'required|exists:assets,id',
                'counted_quantity' => 'required|integer|min:0',
                'condition' => 'nullable|in:new,good,fair,poor,damaged',
                'notes' => 'nullable|string|max:1000',
                'location_found' => 'nullable|exists:locations,id',
                'photo' => 'nullable|string' // Base64 encoded photo
            ]);

            // Skontroluj či už existuje záznam
            $existingCount = InventoryCount::where('inventory_group_id', $validated['inventory_group_id'])
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
     * Získa prehľad inventúry pre skupinu
     *
     * @param InventoryGroup $group
     * @return JsonResponse
     */
    public function getInventoryOverview(InventoryGroup $group): JsonResponse
    {
        try {
            // Celkový počet assets priradených skupine (podľa lokácií/kategórií)
            $totalAssets = $this->getTotalAssetsForGroup($group);
            
            // Počet už spočítaných assets
            $countedAssets = InventoryCount::where('inventory_group_id', $group->id)->count();
            
            // Počet rozdielov
            $differences = InventoryCount::where('inventory_group_id', $group->id)
                ->whereColumn('counted_quantity', '!=', 'expected_quantity')
                ->count();
            
            // Progress
            $progress = $totalAssets > 0 ? round(($countedAssets / $totalAssets) * 100, 1) : 0;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'total_assets' => $totalAssets,
                    'counted_assets' => $countedAssets,
                    'remaining_assets' => $totalAssets - $countedAssets,
                    'differences_found' => $differences,
                    'progress_percent' => $progress,
                    'status' => $group->status,
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
     * Pomocná funkcia na získanie celkového počtu assets pre skupinu
     */
    private function getTotalAssetsForGroup(InventoryGroup $group): int
    {
        // Tu by bola logika na základe priradených lokácií/kategórií
        // Pre jednoduchosť vrátime počet všetkých assets
        return \App\Models\Asset::count();
    }
}