<?php

namespace App\Http\Controllers;

use App\Models\InventoryPlan;
use App\Models\Location;
use App\Models\Category;
use App\Http\Requests\StoreInventoryPlanRequest;
use App\Http\Requests\UpdateInventoryPlanRequest;
use App\Services\CommissionAutoAssignmentService;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use Maatwebsite\Excel\Facades\Excel;

class InventoryPlanController extends Controller
{
    public function index()
    {
        $query = InventoryPlan::with([
            'location:id,name', 
            'category:id,name', 
            'createdBy:id,name', 
            'responsiblePerson:id,name',
            'commission:id,name,chairman_id',
            'commission.chairman:id,name'
        ]);

        // Ak nie je admin s oprávneniami, zobraz len plány komisií kde je používateľ členom alebo predsedom
        if (!auth()->user()->hasAdminPrivileges()) {
            $userId = auth()->id();
            $query->whereHas('commission', function($q) use ($userId) {
                $q->where('chairman_id', $userId)
                  ->orWhereHas('members', function($subQ) use ($userId) {
                      $subQ->where('user_id', $userId);
                  });
            });
        }

        // Skrýj archivované plány ako predvolené nastavenie
        $showArchived = request()->get('show_archived', false);
        if (!$showArchived) {
            $query->where('status', '!=', InventoryPlan::STATUS_ARCHIVED);
        }
        
        $plans = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('inventory_plans.index', compact('plans'));
    }

    public function create()
    {
        // Optimalizované načítanie - iba potrebné stĺpce, zoradené
        $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        $locations = \App\Models\Location::select('id', 'name')->orderBy('name')->get();
        $categories = \App\Models\Category::select('id', 'name')->orderBy('name')->get();
        $commissions = \App\Models\InventoryCommission::with('chairman:id,name')
                            ->select('id', 'name', 'chairman_id')
                            ->orderBy('name')
                            ->get();
        return view('inventory_plans.create', compact('users', 'locations', 'categories', 'commissions'));
    }

    public function store(StoreInventoryPlanRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        // Keďže komisiu priradíme manuálne, status je "assigned" (priradený komisii)
        $validated['status'] = InventoryPlan::STATUS_ASSIGNED;
        
        // Extrahujeme location_ids pred create operáciou (pre filtráciu assetov)
        $locationIds = $validated['location_ids'] ?? [];
        if (isset($validated['location_ids'])) {
            unset($validated['location_ids']);
        }
        
        // Extrahujeme category_id pre filtráciu (ak je iný než plán kategória)
        $filterCategoryId = $validated['category_id'] ?? null;
        if (isset($validated['category_id'])) {
            unset($validated['category_id']);
        }
        
        // Prekonvertujeme plan_category_id na category_id pre databázu
        if (isset($validated['plan_category_id'])) {
            $validated['category_id'] = $validated['plan_category_id'];
            unset($validated['plan_category_id']);
        }
        
        $plan = InventoryPlan::create($validated);

        // Priradíme lokácie k plánu (many-to-many vzťah)
        if (!empty($locationIds)) {
            $plan->locations()->attach($locationIds);
        }

        // Automatické vytvorenie položiek plánu na základe filtrov
        $itemsCount = $this->createPlanItems($plan, $locationIds, $filterCategoryId);

        // Komisia sa už priradila cez validated data (commission_id je už v $validated)
        // Nepoužívame automatické priraďovanie

        \Log::info('InventoryPlan created', [
            'user_id' => auth()->id(),
            'plan_id' => $plan->id,
            'location_ids' => $locationIds,
            'category_id' => $validated['category_id'] ?? null,
            'commission_id' => $plan->commission_id,
            'data' => $validated,
            'action' => 'create',
            'timestamp' => now(),
        ]);

        $message = "Inventúrny plán bol vytvorený s {$itemsCount} položkami.";
        
        // Načítame komisiu pre správu
        $plan->load('commission:id,name');
        if ($plan->commission) {
            $message .= " Priradený komisii: {$plan->commission->name}.";
        }
        
        return redirect()->route('inventory_plans.show', $plan)
            ->with('success', $message);
    }

    /**
     * Vytvorí položky plánu na základe filtrov lokácií a kategórií
     */
    private function createPlanItems(InventoryPlan $plan, array $locationIds = [], $categoryId = null)
    {
        $query = \App\Models\Asset::query();
        
        // Filtr podľa lokácií
        if (!empty($locationIds)) {
            $query->whereIn('location_id', $locationIds);
        }
        
        // Filtr podľa kategórie
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        // Získame assety ktoré ešte nie sú v žiadnom aktívnom pláne
        $existingAssetIds = \App\Models\InventoryPlanItem::whereHas('plan', function($q) {
            $q->whereIn('status', ['planned', 'approved', 'assigned', 'in_progress']);
        })->pluck('asset_id')->toArray();
        
        if (!empty($existingAssetIds)) {
            $query->whereNotIn('id', $existingAssetIds);
        }
        
        $assets = $query->get();
        
        // Vytvoríme položky plánu
        $planItems = [];
        foreach ($assets as $asset) {
            $planItems[] = [
                'inventory_plan_id' => $plan->id,
                'asset_id' => $asset->id,
                'expected_qty' => 1,
                'assignment_status' => 'unassigned',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        if (!empty($planItems)) {
            \App\Models\InventoryPlanItem::insert($planItems);
        }
        
        return count($planItems);
    }

    public function show(InventoryPlan $inventoryPlan)
    {
        $inventoryPlan->load([
            'location:id,name',
            'locations:id,name', 
            'category:id,name', 
            'createdBy:id,name', 
            'updatedBy:id,name',
            'responsiblePerson:id,name',
            'commission:id,name,chairman_id',
            'commission.chairman:id,name',
            'commission.members:id,name',
            'items.asset:id,name,inventory_number,description',
            'items.asset.category:id,name',
            'items.asset.location:id,name',
            'items.counts.counter:id,name'
        ]);
        
        // Počítanie štatistík
        $stats = [
            'total_items' => $inventoryPlan->items()->count(),
            'assigned_items' => $inventoryPlan->items()->whereNotNull('commission_id')->count(),
            'completed_items' => $inventoryPlan->items()->whereIn('assignment_status', [
                \App\Models\InventoryPlanItem::ASSIGNMENT_COMPLETED,
                \App\Models\InventoryPlanItem::ASSIGNMENT_VERIFIED
            ])->count(),
            'total_commissions' => $inventoryPlan->commission ? 1 : 0,
        ];        return view('inventory_plans.show', compact('inventoryPlan', 'stats'));
    }

    public function edit(InventoryPlan $inventoryPlan)
    {
        // Načítanie plánu s lokáciami
        $inventoryPlan->load('locations');
        
        // Optimalizované načítanie - iba potrebné stĺpce, zoradené
        $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        $locations = Location::select('id', 'name')->orderBy('name')->get();
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $commissions = \App\Models\InventoryCommission::with('chairman:id,name')
            ->select('id', 'name', 'chairman_id')
            ->orderBy('name')
            ->get();
            
        return view('inventory_plans.edit', compact('inventoryPlan', 'users', 'locations', 'categories', 'commissions'));
    }

    public function update(UpdateInventoryPlanRequest $request, InventoryPlan $inventoryPlan)
    {
        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();
        
        // Oddelenie location_ids od ostatných dát
        $locationIds = $validated['location_ids'] ?? [];
        unset($validated['location_ids']);
        
        $inventoryPlan->update($validated);
        
        // Aktualizácia lokácií
        if (!empty($locationIds)) {
            $inventoryPlan->locations()->sync($locationIds);
        } else {
            $inventoryPlan->locations()->detach();
        }

        \Log::info('InventoryPlan updated', [
            'user_id' => auth()->id(),
            'plan_id' => $inventoryPlan->id,
            'data' => $validated,
            'location_ids' => $locationIds,
            'action' => 'update',
            'timestamp' => now(),
        ]);

        return redirect()->route('inventory_plans.show', $inventoryPlan)->with('success', 'Inventúrny plán bol upravený.');
    }

    public function destroy(InventoryPlan $inventoryPlan)
    {
        // Kontrola, či plán nie je v stave, ktorý neumožňuje vymazanie
        if ($inventoryPlan->status === InventoryPlan::STATUS_IN_PROGRESS) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nie je možné vymazať inventarizačný plán, ktorý práve prebieha.'
                ], 422);
            }
            return redirect()->route('inventory_plans.index')->with('error', 'Nie je možné vymazať inventarizačný plán, ktorý práve prebieha.');
        }

        // Dodatočné potvrdenie pre podpísané plány
        if ($inventoryPlan->status === InventoryPlan::STATUS_SIGNED) {
            $confirmParam = request()->get('confirm_signed');
            if (!$confirmParam || $confirmParam !== 'yes') {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vymazanie podpísaného inventarizačného plánu vyžaduje dodatočné potvrdenie.',
                        'requires_confirmation' => true,
                        'plan_status' => 'signed'
                    ], 422);
                }
                return redirect()->route('inventory_plans.index')->with('error', 'Vymazanie podpísaného inventarizačného plánu vyžaduje dodatočné potvrdenie.');
            }
        }

        $planName = $inventoryPlan->name;
        $planId = $inventoryPlan->id;
        
        try {
            $inventoryPlan->delete();

            \Log::info('InventoryPlan deleted', [
                'user_id' => auth()->id(),
                'plan_id' => $planId,
                'plan_name' => $planName,
                'action' => 'delete',
                'timestamp' => now(),
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Inventarizačný plán '{$planName}' bol úspešne vymazaný."
                ]);
            }

            return redirect()->route('inventory_plans.index')->with('success', "Inventarizačný plán '{$planName}' bol úspešne vymazaný.");
            
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chyba pri mazaní plánu: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('inventory_plans.index')->with('error', 'Chyba pri mazaní plánu: ' . $e->getMessage());
        }
    }

    // Export inventúrny súpis
    public function exportSoupis(InventoryPlan $inventoryPlan)
    {
        return view('inventory_plans.export.soupis', [
            'plan' => $inventoryPlan,
            // ...other data as needed...
        ]);
    }

    // Export inventarizačný zápis
    public function exportZapis(InventoryPlan $inventoryPlan)
    {
        return view('inventory_plans.export.zapis', [
            'plan' => $inventoryPlan,
            // ...other data as needed...
        ]);
    }

    public function exportSoupisPdf(InventoryPlan $inventoryPlan)
    {
        set_time_limit(120);
        $inventoryPlan->load([
            'items.asset.category',
            'items.asset.location',
            'responsiblePerson'
        ]);
        $html = view('inventory_plans.export.soupis', [
            'plan' => $inventoryPlan,
        ])->render();
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);
        return response($mpdf->Output('inventurny_soupis_'.$inventoryPlan->id.'.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="inventurny_soupis_'.$inventoryPlan->id.'.pdf"');
    }

    public function exportSoupisXlsx(InventoryPlan $inventoryPlan)
    {
        $inventoryPlan->load([
            'items.asset.category',
            'items.asset.location',
            'responsiblePerson'
        ]);
        return Excel::download(new \App\Exports\InventorySoupisExport($inventoryPlan), 'inventurny_soupis_'.$inventoryPlan->id.'.xlsx');
    }


    public function exportZapisPdf(InventoryPlan $inventoryPlan)
    {
        set_time_limit(120);
        $inventoryPlan->load([
            'items.asset.category',
            'items.asset.location',
            'responsiblePerson'
        ]);
        $html = view('inventory_plans.export.zapis', [
            'plan' => $inventoryPlan,
        ])->render();
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);
        return response($mpdf->Output('inventarizacny_zapis_'.$inventoryPlan->id.'.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="inventarizacny_zapis_'.$inventoryPlan->id.'.pdf"');
    }

    public function exportZapisXlsx(InventoryPlan $inventoryPlan)
    {
        $inventoryPlan->load([
            'items.asset.category',
            'items.asset.location',
            'responsiblePerson'
        ]);
        return Excel::download(new \App\Exports\InventoryZapisExport($inventoryPlan), 'inventarizacny_zapis_'.$inventoryPlan->id.'.xlsx');
    }

    /**
     * Zobrazenie formulára pre priraďovanie komisií
     */
    public function assignCommission(InventoryPlan $inventoryPlan)
    {
        $commissions = \App\Models\InventoryCommission::with(['chairman', 'members'])
            ->active()
            ->orderBy('name')
            ->get();

        return view('inventory_plans.assign_commission', compact('inventoryPlan', 'commissions'));
    }

    /**
     * Priradenie komisie k inventarizačnému plánu
     */
    public function storeCommissionAssignment(Request $request, InventoryPlan $inventoryPlan)
    {
        $validated = $request->validate([
            'commission_id' => 'required|exists:inventory_commissions,id'
        ]);

        try {
            $inventoryPlan->assignToCommission($validated['commission_id'], auth()->id());

            // Force JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Komisia bola úspešne priradená k inventarizačnému plánu.',
                    'plan' => $inventoryPlan->fresh(['commission.chairman', 'commission.members'])
                ]);
            }

            return redirect()->route('inventory_plans.index')
                ->with('success', 'Komisia bola úspešne priradená k inventarizačnému plánu.');

        } catch (\Exception $e) {
            // Force JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chyba pri priraďovaní komisie: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Chyba pri priraďovaní komisie: ' . $e->getMessage());
        }
    }

    /**
     * Spustenie inventarizácie
     */
    public function startInventory(InventoryPlan $inventoryPlan)
    {
        try {
            $inventoryPlan->startInventory(auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Inventarizácia bola úspešne spustená.',
                'plan' => $inventoryPlan->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Dokončenie inventarizácie
     */
    public function completeInventory(InventoryPlan $inventoryPlan)
    {
        try {
            $inventoryPlan->completeInventory(auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Inventarizácia bola úspešne dokončená.',
                'plan' => $inventoryPlan->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Podpísanie inventarizácie komisiou
     */
    public function signInventory(InventoryPlan $inventoryPlan)
    {
        try {
            $inventoryPlan->signInventory(auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Inventarizácia bola úspešne podpísaná.',
                'plan' => $inventoryPlan->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Archivácia inventarizácie
     */
    public function archiveInventory(InventoryPlan $inventoryPlan)
    {
        try {
            $inventoryPlan->archiveInventory(auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Inventarizácia bola úspešne archivovaná.',
                'plan' => $inventoryPlan->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Odstránenie priradenia komisie
     */
    public function removeCommissionAssignment(InventoryPlan $inventoryPlan)
    {
        try {
            $inventoryPlan->update([
                'commission_id' => null,
                'status' => InventoryPlan::STATUS_APPROVED,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Priradenie komisie bolo úspešne odstránené.',
                'plan' => $inventoryPlan->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri odstraňovaní priradenia: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Dashboard pre commission workflow
     */
    public function commissionDashboard()
    {
        $unassignedPlans = InventoryPlan::unassigned()
            ->where('status', InventoryPlan::STATUS_APPROVED)
            ->with(['location', 'category', 'responsiblePerson'])
            ->orderBy('date_start')
            ->get();

        $assignedPlans = InventoryPlan::assigned()
            ->with(['commission.chairman', 'commission.members', 'location', 'responsiblePerson'])
            ->orderBy('date_start')
            ->get();

        $commissions = \App\Models\InventoryCommission::with(['chairman', 'members'])
            ->withCount('inventoryPlans')
            ->active()
            ->orderBy('name')
            ->get();

        return view('inventory_plans.commission_dashboard', compact(
            'unassignedPlans', 
            'assignedPlans', 
            'commissions'
        ));
    }

    /**
     * Automatické priradenie komisie k inventarizačnému plánu
     */
    public function autoAssignCommission(InventoryPlan $inventoryPlan)
    {
        try {
            $autoAssignmentService = app(CommissionAutoAssignmentService::class);
            $assignedCommission = $autoAssignmentService->assignCommission($inventoryPlan);

            if ($assignedCommission) {
                return response()->json([
                    'success' => true,
                    'message' => "Plán bol automaticky priradený komisii: {$assignedCommission->name}",
                    'commission' => [
                        'id' => $assignedCommission->id,
                        'name' => $assignedCommission->name
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Nepodarilo sa nájsť vhodnú komisiu pre automatické priradenie.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri automatickom priraďovaní: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Automatické priradenie komisií pre všetky nepriradené plány
     */
    public function autoAssignAllCommissions()
    {
        try {
            $unassignedPlans = InventoryPlan::unassigned()
                ->where('status', InventoryPlan::STATUS_APPROVED)
                ->get();

            if ($unassignedPlans->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Žiadne nepriradené plány na automatické priradenie.'
                ]);
            }

            $autoAssignmentService = app(CommissionAutoAssignmentService::class);
            $assignedCount = 0;
            $totalCount = $unassignedPlans->count();

            foreach ($unassignedPlans as $plan) {
                $assignedCommission = $autoAssignmentService->assignCommission($plan);
                if ($assignedCommission) {
                    $assignedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Automatické priraďovanie dokončené.",
                'assigned_count' => $assignedCount,
                'total_count' => $totalCount,
                'success_rate' => round(($assignedCount / $totalCount) * 100, 1)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri automatickom priraďovaní: ' . $e->getMessage()
            ], 500);
        }
    }
}

