<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AssetApiController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Zobrazí zoznam všetkého majetku
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Asset::with(['category:id,name', 'location:id,name']);
            
            // Filtrovanie
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            
            if ($request->filled('location_id')) {
                $query->where('location_id', $request->location_id);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('inventory_number', 'LIKE', "%{$search}%");
                });
            }
            
            // Paginácia
            $perPage = min($request->get('per_page', 15), 100); // Max 100 items per page
            $assets = $query->orderBy('name')->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $assets->items(),
                'meta' => [
                    'current_page' => $assets->currentPage(),
                    'from' => $assets->firstItem(),
                    'last_page' => $assets->lastPage(),
                    'per_page' => $assets->perPage(),
                    'to' => $assets->lastItem(),
                    'total' => $assets->total(),
                ]
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
     * Zobrazí detail majetku
     *
     * @param Asset $asset
     * @return JsonResponse
     */
    public function show(Asset $asset): JsonResponse
    {
        try {
            $asset->load(['category:id,name', 'location:id,name']);
            
            return response()->json([
                'success' => true,
                'data' => $asset
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Majetok nenájdený',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 404);
        }
    }

    /**
     * Vytvorí nový majetok
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'inventory_number' => 'required|string|max:50|unique:assets',
                'name' => 'required|string|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'location_id' => 'nullable|exists:locations,id',
                'acquisition_date' => 'required|date',
                'acquisition_cost' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:1000',
                'responsible_person' => 'nullable|string|max:100',
                'serial_number' => 'nullable|string|max:100',
                'warranty_until' => 'nullable|date',
                'purchase_price' => 'nullable|numeric|min:0',
                'supplier' => 'nullable|string|max:255',
                'funding_source' => 'nullable|string|max:100',
                'condition' => 'nullable|in:new,good,fair,poor,damaged',
                'notes' => 'nullable|string|max:1000'
            ]);

            $asset = Asset::create($validated);
            $asset->load(['category:id,name', 'location:id,name']);

            return response()->json([
                'success' => true,
                'message' => 'Majetok bol úspešne vytvorený',
                'data' => $asset
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validačné chyby',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri vytváraní majetku',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Aktualizuje majetok
     *
     * @param Request $request
     * @param Asset $asset
     * @return JsonResponse
     */
    public function update(Request $request, Asset $asset): JsonResponse
    {
        try {
            $validated = $request->validate([
                'inventory_number' => 'required|string|max:50|unique:assets,inventory_number,' . $asset->id,
                'name' => 'required|string|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'location_id' => 'nullable|exists:locations,id',
                'acquisition_date' => 'required|date',
                'acquisition_cost' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:1000',
                'responsible_person' => 'nullable|string|max:100',
                'serial_number' => 'nullable|string|max:100',
                'warranty_until' => 'nullable|date',
                'purchase_price' => 'nullable|numeric|min:0',
                'supplier' => 'nullable|string|max:255',
                'funding_source' => 'nullable|string|max:100',
                'condition' => 'nullable|in:new,good,fair,poor,damaged',
                'notes' => 'nullable|string|max:1000'
            ]);

            $asset->update($validated);
            $asset->load(['category:id,name', 'location:id,name']);

            return response()->json([
                'success' => true,
                'message' => 'Majetok bol úspešne aktualizovaný',
                'data' => $asset
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
                'message' => 'Chyba pri aktualizácii majetku',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Zmaže majetok
     *
     * @param Asset $asset
     * @return JsonResponse
     */
    public function destroy(Asset $asset): JsonResponse
    {
        try {
            $asset->delete();

            return response()->json([
                'success' => true,
                'message' => 'Majetok bol úspešne vymazaný'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri mazaní majetku',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Vyhľadá majetok podľa QR kódu alebo čiarového kódu
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function scanCode(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'required|string'
            ]);

            $code = $request->code;
            
            // Vyhľadaj podľa inventory_number alebo serial_number
            $asset = Asset::with(['category:id,name', 'location:id,name'])
                ->where('inventory_number', $code)
                ->orWhere('serial_number', $code)
                ->first();

            if (!$asset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Majetok s týmto kódom nebol nájdený',
                    'code' => $code
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Majetok nájdený',
                'data' => $asset
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
                'message' => 'Chyba pri vyhľadávaní majetku',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}