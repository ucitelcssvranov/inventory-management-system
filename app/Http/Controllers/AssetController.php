<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Services\InventoryNumberService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('category', 'location');
        
        // Filter podľa vlastníka
        if ($request->filled('owner')) {
            $query->where('owner', $request->owner);
        }
        
        // Filter podľa názvu
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // Filter podľa inventárneho čísla
        if ($request->filled('inventory_number')) {
            $query->where('inventory_number', 'like', '%' . $request->inventory_number . '%');
        }
        
        // Filter podľa sériového čísla
        if ($request->filled('serial_number')) {
            $query->where('serial_number', 'like', '%' . $request->serial_number . '%');
        }
        
        // Filter podľa kategórie
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter podľa stavu
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $assets = $query->orderBy('created_at', 'desc')->get();
        
        // Zoskupenie assetov podľa názvu a lokácie
        $groupedAssets = $assets->groupBy(function ($asset) {
            return $asset->name . '|' . ($asset->location ? $asset->location->name : 'Bez lokácie');
        })->map(function ($group) {
            $firstAsset = $group->first();
            return [
                'name' => $firstAsset->name,
                'category' => $firstAsset->category,
                'location' => $firstAsset->location,
                'count' => $group->count(),
                'assets' => $group,
                'total_cost' => $group->sum('acquisition_cost'),
                'statuses' => $group->groupBy('status')->map->count(),
                'commissions' => $group->pluck('inventory_commission')->filter()->unique(),
                'owners' => $group->pluck('owner')->filter()->unique()
            ];
        })->values();
        
        $categories = \App\Models\Category::orderBy('name')->get(['id', 'name']);
        
        // Získame unikátne hodnoty pre dropdowny              
        $owners = Asset::whereNotNull('owner')
                      ->distinct()
                      ->pluck('owner')
                      ->sort()
                      ->values();
        
        // Debug výpis
        \Log::info('Assets Controller - Owners count: ' . $owners->count());
        \Log::info('Assets Controller - Owners data: ', $owners->toArray());
        
        return view('assets.index', compact('groupedAssets', 'categories', 'owners'));
    }

    public function create()
    {
        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);
        $categories = Category::orderBy('name')->get(['id', 'name', 'description']);
        // Už nepotrebujeme locations, lebo sa načítajú cez AJAX
        return view('assets.create', compact('users', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'serial_number' => 'nullable|string|max:255',
            'building_id' => 'nullable|exists:locations,id',
            'location_id' => 'required|exists:locations,id',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'residual_value' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,written_off,in_repair,lost',
            'description' => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        // Konvertuje owner_id na meno užívateľa pre uloženie ako string
        if ($validated['owner_id']) {
            $user = \App\Models\User::find($validated['owner_id']);
            $validated['owner'] = $user ? $user->name : null;
        }
        unset($validated['owner_id']);

        // Odstráni building_id z validated dát, lebo sa neukladá do databázy
        unset($validated['building_id']);
        
        // Inventárne číslo sa bude generovať automaticky
        // Nastavíme prázdne inventárne číslo, ktoré sa vygeneruje v modeli
        $validated['inventory_number'] = '';
        $validated['created_by'] = auth()->id();
        
        // Nastavíme default komisiu
        $validated['inventory_commission'] = 'Informatika';
        
        Asset::create($validated);

        return redirect()->route('assets.index')->with('success', 'Asset created successfully.');
    }

    public function show(Asset $asset)
    {
        $asset->load('category', 'location', 'createdBy', 'updatedBy');
        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);
        $categories = Category::orderBy('name')->get(['id', 'name', 'description']);
        // Už nepotrebujeme locations, lebo sa načítajú cez AJAX
        return view('assets.edit', compact('asset', 'users', 'categories'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'serial_number' => 'nullable|string|max:255',
            'building_id' => 'nullable|exists:locations,id',
            'location_id' => 'required|exists:locations,id',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'residual_value' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,written_off,in_repair,lost',
            'description' => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        // Konvertuje owner_id na meno užívateľa pre uloženie ako string
        if ($validated['owner_id']) {
            $user = \App\Models\User::find($validated['owner_id']);
            $validated['owner'] = $user ? $user->name : null;
        }
        unset($validated['owner_id']);

        // Odstráni building_id z validated dát, lebo sa neukladá do databázy
        unset($validated['building_id']);
        
        $validated['updated_by'] = auth()->id();
        
        // Ak sa zmenil dátum nadobudnutia, regeneruj inventárne číslo
        if ($asset->acquisition_date != $validated['acquisition_date']) {
            $asset->update($validated);
            $asset->regenerateInventoryNumber();
        } else {
            $asset->update($validated);
        }

        return redirect()->route('assets.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }

    /**
     * AJAX endpoint pre generovanie dočasného inventárneho čísla
     */
    public function generateInventoryNumber(Request $request)
    {
        $request->validate([
            'acquisition_date' => 'required|date'
        ]);

        try {
            $inventoryNumber = Asset::generateTemporaryInventoryNumber($request->acquisition_date);
            
            return response()->json([
                'success' => true,
                'inventory_number' => $inventoryNumber
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri generovaní inventárneho čísla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX endpoint pre načítanie existujúcich vlastníkov
     */
    public function getOwners(Request $request)
    {
        try {
            $owners = Asset::whereNotNull('owner')
                          ->where('owner', '!=', '')
                          ->distinct()
                          ->pluck('owner')
                          ->sort()
                          ->values();
            
            return response()->json([
                'success' => true,
                'owners' => $owners
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítaní vlastníkov: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hromadné operácie s assetmi
     */
    public function bulkOperation(Request $request)
    {
        $validated = $request->validate([
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:assets,id',
            'operation' => 'required|in:change_status,change_location,change_owner,delete',
            'new_status' => 'required_if:operation,change_status|in:active,written_off,in_repair,lost',
            'new_location_id' => 'required_if:operation,change_location|exists:locations,id',
            'new_owner_id' => 'required_if:operation,change_owner|exists:users,id',
        ]);

        try {
            $assetIds = $validated['asset_ids'];
            $operation = $validated['operation'];
            $affectedCount = 0;

            switch ($operation) {
                case 'change_status':
                    $affectedCount = Asset::whereIn('id', $assetIds)
                                         ->update(['status' => $validated['new_status'], 'updated_by' => auth()->id()]);
                    $message = "Stav zmenený pre {$affectedCount} položiek majetku.";
                    break;

                case 'change_location':
                    $location = \App\Models\Location::find($validated['new_location_id']);
                    $affectedCount = Asset::whereIn('id', $assetIds)
                                         ->update(['location_id' => $validated['new_location_id'], 'updated_by' => auth()->id()]);
                    $message = "Lokácia zmenená na '{$location->name}' pre {$affectedCount} položiek majetku.";
                    break;

                case 'change_owner':
                    $user = \App\Models\User::find($validated['new_owner_id']);
                    $affectedCount = Asset::whereIn('id', $assetIds)
                                         ->update(['owner' => $user->name, 'updated_by' => auth()->id()]);
                    $message = "Vlastník zmenený na '{$user->name}' pre {$affectedCount} položiek majetku.";
                    break;

                case 'delete':
                    $affectedCount = Asset::whereIn('id', $assetIds)->delete();
                    $message = "Vymazaných {$affectedCount} položiek majetku.";
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'affected_count' => $affectedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri vykonávaní hromadnej operácie: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zobrazí formulár pre hromadnú operáciu
     */
    public function bulkOperationForm(Request $request)
    {
        $assetIds = $request->input('asset_ids', []);
        
        if (empty($assetIds)) {
            return redirect()->route('assets.index')->with('error', 'Nevybrali ste žiadne položky majetku.');
        }

        $assets = Asset::with(['category', 'location'])->whereIn('id', $assetIds)->get();
        $locations = \App\Models\Location::orderBy('name')->get(['id', 'name']);
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('assets.bulk-operation', compact('assets', 'locations', 'users', 'assetIds'));
    }

    /**
     * Generate QR code for asset
     */
    public function generateQrCode(Asset $asset, QrCodeService $qrCodeService)
    {
        try {
            $filename = $qrCodeService->generateQrCode($asset);
            
            if ($filename) {
                return response()->json([
                    'success' => true,
                    'message' => 'QR kód bol úspešne vygenerovaný',
                    'qr_code_url' => $qrCodeService->getQrCodeUrl($asset)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'QR kód sa nepodarilo vygenerovať'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri generovaní QR kódu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk generate QR codes
     */
    public function bulkGenerateQrCodes(Request $request, QrCodeService $qrCodeService)
    {
        $request->validate([
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'exists:assets,id'
        ]);

        try {
            $results = $qrCodeService->generateQrCodes($request->asset_ids);
            $successful = array_filter($results, fn($r) => $r['success']);
            $failed = array_filter($results, fn($r) => !$r['success']);

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    'Vygenerovaných %d z %d QR kódov',
                    count($successful),
                    count($results)
                ),
                'results' => $results,
                'summary' => [
                    'total' => count($results),
                    'successful' => count($successful),
                    'failed' => count($failed)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri hromadnom generovaní QR kódov: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show QR codes for printing
     */
    public function showQrCodes(Request $request, QrCodeService $qrCodeService)
    {
        $assetIds = $request->input('asset_ids', []);
        
        if (empty($assetIds)) {
            return redirect()->route('assets.index')->with('error', 'Nevybrali ste žiadne položky majetku.');
        }

        $qrCodes = $qrCodeService->generatePrintView($assetIds);
        
        return view('assets.qr-codes-print', compact('qrCodes'));
    }

    /**
     * Get QR code statistics
     */
    public function qrCodeStats(QrCodeService $qrCodeService)
    {
        try {
            $stats = $qrCodeService->getStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítavaní štatistík: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asset scan endpoint for QR codes
     */
    public function scan(Asset $asset)
    {
        // This endpoint is called when QR code is scanned
        return redirect()->route('assets.show', $asset)
                        ->with('success', 'Majetok bol načítaný zo QR kódu');
    }
}
