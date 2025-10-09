<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use App\Models\Category;
use App\Models\InventoryCommission;
use App\Models\InventoryPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AjaxController extends Controller
{
    /**
     * Načíta lokácie pre danú kategóriu/parent ID
     * categoryId = 0: vráti všetky budovy
     * categoryId > 0: vráti miestnosti pre danú budovu
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getLocationsByCategory(int $categoryId): JsonResponse
    {
        try {
            if ($categoryId == 0) {
                // Vráti všetky budovy (locations bez parent_id)
                $locations = Location::whereNull('parent_id')
                    ->where('type', 'budova')
                    ->orderBy('name')
                    ->get(['id', 'name', 'type']);
                    
                $responseType = 'budovy';
            } else {
                // Vráti miestnosti pre danú budovu (parent_id = categoryId)
                $locations = Location::where('parent_id', $categoryId)
                    ->where('type', 'miestnost')
                    ->orderBy('room_number')
                    ->get(['id', 'name', 'room_number', 'parent_id']);
                    
                // Pridáme room_number do názvu pre lepšiu identifikáciu
                $locations = $locations->map(function ($location) {
                    $location->display_name = $location->room_number 
                        ? $location->room_number . ' - ' . $location->name 
                        : $location->name;
                    return $location;
                });
                
                $responseType = 'miestnosti';
            }

            return response()->json([
                'success' => true,
                'locations' => $locations,
                'count' => $locations->count(),
                'type' => $responseType,
                'categoryId' => $categoryId
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
     * Načíta assets pre danú lokáciu
     *
     * @param int $locationId
     * @return JsonResponse
     */
    public function getAssetsByLocation(int $locationId): JsonResponse
    {
        try {
            $assets = Asset::where('location_id', $locationId)
                ->with(['category:id,name'])
                ->orderBy('name')
                ->select(['id', 'name', 'inventory_number', 'category_id', 'purchase_price'])
                ->get();

            return response()->json([
                'success' => true,
                'assets' => $assets,
                'count' => $assets->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní majetku',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Načíta všetky budovy
     *
     * @return JsonResponse
     */
    public function getBuildings(): JsonResponse
    {
        try {
            $buildings = Location::whereNull('parent_id')
                ->where('type', 'budova')
                ->orderBy('name')
                ->get(['id', 'name', 'type']);

            return response()->json([
                'success' => true,
                'buildings' => $buildings,
                'count' => $buildings->count(),
                'type' => 'budovy'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní budov',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Načíta miestnosti pre danú budovu
     *
     * @param int $buildingId
     * @return JsonResponse
     */
    public function getRoomsByBuilding(int $buildingId): JsonResponse
    {
        try {
            $rooms = Location::where('parent_id', $buildingId)
                ->where('type', 'miestnost')
                ->orderBy('room_number')
                ->get(['id', 'name', 'room_number', 'parent_id']);
                
            // Pridáme room_number do názvu pre lepšiu identifikáciu
            $rooms = $rooms->map(function ($room) {
                $room->display_name = $room->room_number 
                    ? $room->room_number . ' - ' . $room->name 
                    : $room->name;
                return $room;
            });

            return response()->json([
                'success' => true,
                'rooms' => $rooms,
                'count' => $rooms->count(),
                'type' => 'miestnosti',
                'buildingId' => $buildingId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní miestností',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Načíta používateľov pre danú komisiu
     *
     * @param int $commissionId
     * @return JsonResponse
     */
    public function getUsersByCommission(int $commissionId): JsonResponse
    {
        try {
            $commission = InventoryCommission::with('users:id,first_name,last_name,email')->find($commissionId);
            $users = $commission ? $commission->users : collect();

            return response()->json([
                'success' => true,
                'users' => $users,
                'commission' => [
                    'id' => $commission->id ?? null,
                    'name' => $commission->name ?? null
                ],
                'count' => $users->count(),
                'cached' => false
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
     * Vyhľadá používateľov podľa mena
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchUsers(Request $request): JsonResponse
    {
        try {
            $search = $request->get('q', '');
            $limit = min($request->get('limit', 20), 50); // Max 50 results
            
            // Pre vyhľadávanie nepoužívame cache, lebo je dynamické
            $users = User::where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orderBy('name')
                ->limit($limit)
                ->select(['id', 'name', 'email'])
                ->get();

            return response()->json([
                'success' => true,
                'users' => $users,
                'count' => $users->count(),
                'search' => $search,
                'cached' => false
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri vyhľadávaní používateľov',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Načíta zoznam všetkých kategórií
     *
     * @return JsonResponse
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = Category::orderBy('name')->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'categories' => $categories,
                'count' => $categories->count(),
                'cached' => true
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
     * Načíta zoznam všetkých lokácií
     *
     * @return JsonResponse
     */
    public function getLocations(): JsonResponse
    {
        try {
            $locations = Location::with('category:id,name')->orderBy('name')->get(['id', 'name', 'category_id']);

            return response()->json([
                'success' => true,
                'locations' => $locations,
                'count' => $locations->count(),
                'cached' => true
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
     * Načíta štatistiky pre dashboard
     *
     * @return JsonResponse
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            $stats = [
                'total_assets' => Asset::count(),
                'categories_count' => Category::count(),
                'locations_count' => Location::count(),
                'pending_plans' => InventoryPlan::where('status', 'pending')->count(),
                'active_plans' => InventoryPlan::where('status', 'active')->count()
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'cached' => true
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
     * Vymaže všetky cache
     *
     * @return JsonResponse
     */
    public function clearCache(): JsonResponse
    {
        try {
            // Dočasne vypnuté - cache service má problémy
            return response()->json([
                'success' => true,
                'message' => 'Cache clearing dočasne vypnuté'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri mazaní cache',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Predhrie cache
     *
     * @return JsonResponse
     */
    public function warmUpCache(): JsonResponse
    {
        try {
            // Dočasne vypnuté - cache service má problémy
            return response()->json([
                'success' => true,
                'message' => 'Cache warming dočasne vypnuté'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri predhriatí cache',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Získa informácie o cache
     *
     * @return JsonResponse
     */
    public function getCacheInfo(): JsonResponse
    {
        try {
            // Dočasne vypnuté - cache service má problémy
            return response()->json([
                'success' => true,
                'cache_info' => ['message' => 'Cache info dočasne nedostupné']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri získavaní informácií o cache',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}