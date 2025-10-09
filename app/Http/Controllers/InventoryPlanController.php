<?php

namespace App\Http\Controllers;

use App\Models\InventoryPlan;
use App\Models\Location;
use App\Models\Category;
use App\Http\Requests\StoreInventoryPlanRequest;
use App\Http\Requests\UpdateInventoryPlanRequest;
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
            'commission.chairman:id,name',
            'commission.members:id,name'
        ]);

        // Ak nie je admin, zobraz len plány komisií kde je používateľ členom alebo predsedom
        if (!auth()->user()->isAdmin()) {
            $userId = auth()->id();
            $query->whereHas('commission', function($q) use ($userId) {
                $q->where('chairman_id', $userId)
                  ->orWhereHas('members', function($subQ) use ($userId) {
                      $subQ->where('user_id', $userId);
                  });
            });
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
        return view('inventory_plans.create', compact('users', 'locations', 'categories'));
    }

    public function store(StoreInventoryPlanRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'planned';
        $plan = InventoryPlan::create($validated);

        \Log::info('InventoryPlan created', [
            'user_id' => auth()->id(),
            'plan_id' => $plan->id,
            'data' => $validated,
            'action' => 'create',
            'timestamp' => now(),
        ]);

        return redirect()->route('inventory_plans.index')->with('success', 'Inventúrny plán bol vytvorený.');
    }

    public function show(InventoryPlan $inventoryPlan)
    {
        $inventoryPlan->load([
            'location:id,name', 
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
        // Optimalizované načítanie - iba potrebné stĺpce, zoradené
        $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        $locations = Location::select('id', 'name')->orderBy('name')->get();
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        return view('inventory_plans.edit', compact('inventoryPlan', 'users', 'locations', 'categories'));
    }

    public function update(UpdateInventoryPlanRequest $request, InventoryPlan $inventoryPlan)
    {
        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();
        $inventoryPlan->update($validated);

        \Log::info('InventoryPlan updated', [
            'user_id' => auth()->id(),
            'plan_id' => $inventoryPlan->id,
            'data' => $validated,
            'action' => 'update',
            'timestamp' => now(),
        ]);

        return redirect()->route('inventory_plans.index')->with('success', 'Inventúrny plán bol upravený.');
    }

    public function destroy(InventoryPlan $inventoryPlan)
    {
        if ($inventoryPlan->status === 'completed' || $inventoryPlan->status === InventoryPlan::STATUS_COMPLETED) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nie je možné vymazať dokončený inventarizačný plán.'
                ], 422);
            }
            return redirect()->route('inventory_plans.index')->with('error', 'Nie je možné vymazať dokončený inventarizačný plán.');
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
}

