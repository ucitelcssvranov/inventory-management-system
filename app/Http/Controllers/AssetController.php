<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Services\InventoryNumberService;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('category', 'location');
        
        // Filter podľa inventarizačnej komisie
        if ($request->filled('commission')) {
            $query->where('inventory_commission', $request->commission);
        }
        
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
        $categories = \App\Models\Category::orderBy('name')->get(['id', 'name']);
        
        // Získame unikátne hodnoty pre dropdowny
        $commissions = Asset::whereNotNull('inventory_commission')
                           ->distinct()
                           ->pluck('inventory_commission')
                           ->sort()
                           ->values();
                           
        $owners = Asset::whereNotNull('owner')
                      ->distinct()
                      ->pluck('owner')
                      ->sort()
                      ->values();
        
        return view('assets.index', compact('assets', 'categories', 'commissions', 'owners'));
    }

    public function create()
    {
        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);
        $inventoryCommissions = \App\Models\InventoryCommission::orderBy('name')->get(['id', 'name', 'description']);
        $categories = Category::orderBy('name')->get(['id', 'name', 'description']);
        // Už nepotrebujeme locations, lebo sa načítajú cez AJAX
        return view('assets.create', compact('users', 'inventoryCommissions', 'categories'));
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
            'inventory_commission' => 'nullable|string|max:255',
            'owner' => 'nullable|string|max:255',
        ]);

        // Odstráni building_id z validated dát, lebo sa neukladá do databázy
        unset($validated['building_id']);
        
        // Inventárne číslo sa bude generovať automaticky
        // Nastavíme prázdne inventárne číslo, ktoré sa vygeneruje v modeli
        $validated['inventory_number'] = '';
        $validated['created_by'] = auth()->id();
        
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
        $inventoryCommissions = \App\Models\InventoryCommission::orderBy('name')->get(['id', 'name', 'description']);
        $categories = Category::orderBy('name')->get(['id', 'name', 'description']);
        // Už nepotrebujeme locations, lebo sa načítajú cez AJAX
        return view('assets.edit', compact('asset', 'users', 'inventoryCommissions', 'categories'));
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
            'inventory_commission' => 'nullable|string|max:255',
            'owner' => 'nullable|string|max:255',
        ]);

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
}
