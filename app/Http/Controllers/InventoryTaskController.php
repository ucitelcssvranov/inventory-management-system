<?php

namespace App\Http\Controllers;

use App\Models\InventoryPlan;
use App\Models\InventoryPlanItem;
use App\Models\InventoryCount;
use App\Models\InventoryCommission;
use Illuminate\Http\Request;

class InventoryTaskController extends Controller
{
    /**
     * Zobrazenie úloh pre aktuálneho používateľa (člena komisie)
     */
    public function index()
    {
        $user = auth()->user();
        
        // Získať komisie používateľa
        $userCommissions = $user->allCommissions();
        
        if ($userCommissions->isEmpty() && !$user->hasAdminPrivileges()) {
            return redirect()->route('home')
                ->with('warning', 'Nie ste členom žiadnej inventarizačnej komisie.');
        }

        // Získať aktívne plány pridelené komisiám používateľa
        $commissionIds = $userCommissions->pluck('id');
        
        $activePlans = InventoryPlan::whereIn('commission_id', $commissionIds)
            ->whereIn('status', [
                InventoryPlan::STATUS_ASSIGNED,
                InventoryPlan::STATUS_IN_PROGRESS
            ])
            ->with([
                'commission:id,name,chairman_id',
                'commission.chairman:id,name',
                'commission.members:id,name',
                'location:id,name',
                'category:id,name',
                'responsiblePerson:id,name'
            ])
            ->orderBy('date_start')
            ->get();

        // Štatistiky pre každý plán
        $planStats = [];
        foreach ($activePlans as $plan) {
            $totalItems = $plan->items()->count();
            $completedItems = $plan->items()
                ->whereIn('assignment_status', [
                    InventoryPlanItem::ASSIGNMENT_COMPLETED,
                    InventoryPlanItem::ASSIGNMENT_VERIFIED
                ])
                ->count();
            
            $planStats[$plan->id] = [
                'total_items' => $totalItems,
                'completed_items' => $completedItems,
                'progress_percentage' => $totalItems > 0 ? round(($completedItems / $totalItems) * 100, 1) : 0,
                'remaining_items' => $totalItems - $completedItems,
                'is_chairman' => $plan->commission->chairman_id == $user->id
            ];
        }

        // Celkové štatistiky používateľa
        $totalStats = [
            'total_plans' => $activePlans->count(),
            'chairman_plans' => $activePlans->where('commission.chairman_id', $user->id)->count(),
            'member_plans' => $activePlans->where('commission.chairman_id', '!=', $user->id)->count(),
            'total_items' => array_sum(array_column($planStats, 'total_items')),
            'completed_items' => array_sum(array_column($planStats, 'completed_items')),
        ];

        $totalStats['overall_progress'] = $totalStats['total_items'] > 0 
            ? round(($totalStats['completed_items'] / $totalStats['total_items']) * 100, 1) 
            : 0;

        return view('inventory-tasks.index', compact(
            'activePlans',
            'planStats', 
            'totalStats',
            'userCommissions'
        ));
    }

    /**
     * Zobrazenie detailu inventarizačného procesu pre konkrétny plán
     */
    public function show(InventoryPlan $plan)
    {
        $user = auth()->user();
        
        // Kontrola oprávnení - používateľ musí byť členom komisie alebo admin
        if (!$user->hasAdminPrivileges() && !$user->isCommissionMember($plan->commission_id)) {
            abort(403, 'Nemáte oprávnenie pristupovať k tomuto inventarizačnému plánu.');
        }

        $plan->load([
            'commission:id,name,chairman_id',
            'commission.chairman:id,name',
            'commission.members:id,name',
            'location:id,name',
            'category:id,name',
            'responsiblePerson:id,name',
            'items.asset:id,name,inventory_number,description,serial_number',
            'items.asset.category:id,name',
            'items.asset.location:id,name',
            'items.counts.counter:id,name'
        ]);

        // Rozdelenie položiek podľa statusu
        $itemsByStatus = [
            'unassigned' => $plan->items->where('assignment_status', InventoryPlanItem::ASSIGNMENT_UNASSIGNED),
            'assigned' => $plan->items->where('assignment_status', InventoryPlanItem::ASSIGNMENT_ASSIGNED),
            'in_progress' => $plan->items->where('assignment_status', InventoryPlanItem::ASSIGNMENT_IN_PROGRESS),
            'completed' => $plan->items->where('assignment_status', InventoryPlanItem::ASSIGNMENT_COMPLETED),
            'verified' => $plan->items->where('assignment_status', InventoryPlanItem::ASSIGNMENT_VERIFIED),
        ];

        // Štatistiky plánu
        $stats = [
            'total_items' => $plan->items->count(),
            'unassigned_items' => $itemsByStatus['unassigned']->count(),
            'assigned_items' => $itemsByStatus['assigned']->count(),
            'in_progress_items' => $itemsByStatus['in_progress']->count(),
            'completed_items' => $itemsByStatus['completed']->count(),
            'verified_items' => $itemsByStatus['verified']->count(),
            'progress_percentage' => $plan->items->count() > 0 
                ? round((($itemsByStatus['completed']->count() + $itemsByStatus['verified']->count()) / $plan->items->count()) * 100, 1) 
                : 0,
        ];

        // Kontrola či je používateľ predseda komisie
        $isChairman = $plan->commission->chairman_id == $user->id;

        return view('inventory-tasks.show', compact(
            'plan',
            'itemsByStatus',
            'stats',
            'isChairman'
        ));
    }

    /**
     * Spustenie inventarizácie položky
     */
    public function startItem(Request $request, InventoryPlanItem $item)
    {
        $user = auth()->user();
        
        // Kontrola oprávnení
        if (!$user->hasAdminPrivileges() && !$user->isCommissionMember($item->plan->commission_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Nemáte oprávnenie pracovať s touto položkou.'
            ], 403);
        }

        // Kontrola či je položka v správnom stave
        if (!in_array($item->assignment_status, [
            InventoryPlanItem::ASSIGNMENT_ASSIGNED,
            InventoryPlanItem::ASSIGNMENT_IN_PROGRESS
        ])) {
            return response()->json([
                'success' => false,
                'message' => 'Položka nie je v stave, ktorý umožňuje spustenie inventarizácie.'
            ], 422);
        }

        try {
            // Aktualizácia statusu položky
            $item->update([
                'assignment_status' => InventoryPlanItem::ASSIGNMENT_IN_PROGRESS,
                'started_at' => now(),
                'started_by' => $user->id
            ]);

            // Aktualizácia statusu plánu ak ešte nie je v progrese
            if ($item->plan->status == InventoryPlan::STATUS_ASSIGNED) {
                $item->plan->update([
                    'status' => InventoryPlan::STATUS_IN_PROGRESS,
                    'started_at' => now(),
                    'started_by' => $user->id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Inventarizácia položky bola spustená.',
                'item' => $item->fresh(['asset', 'asset.location', 'asset.category'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri spustení inventarizácie: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zaznamenie počtu pre položku
     */
    public function recordCount(Request $request, InventoryPlanItem $item)
    {
        $user = auth()->user();
        
        // Kontrola oprávnení
        if (!$user->hasAdminPrivileges() && !$user->isCommissionMember($item->plan->commission_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Nemáte oprávnenie pracovať s touto položkou.'
            ], 403);
        }

        $validated = $request->validate([
            'actual_qty' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'condition' => 'nullable|in:new,good,fair,poor,damaged',
        ]);

        try {
            // Vytvorenie záznamu o počte
            $count = InventoryCount::create([
                'inventory_plan_item_id' => $item->id,
                'asset_id' => $item->asset_id,
                'counted_qty' => $validated['actual_qty'],
                'note' => $validated['notes'], // Použijem 'note' namiesto 'notes'
                'condition' => $validated['condition'] ?? 'good',
                'counted_by' => $user->id,
                'counted_at' => now()
            ]);

            // Aktualizácia statusu položky
            $item->update([
                'assignment_status' => InventoryPlanItem::ASSIGNMENT_COMPLETED,
                'completed_at' => now(),
                'completed_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Počet bol úspešne zaznamenaný.',
                'count' => $count,
                'item' => $item->fresh(['asset', 'counts'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri zaznamenaní počtu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Overenie dokončenej inventarizácie (iba predseda komisie)
     */
    public function verifyItem(Request $request, InventoryPlanItem $item)
    {
        $user = auth()->user();
        
        // Kontrola oprávnení - iba predseda komisie alebo admin
        $isChairman = $item->plan->commission && $item->plan->commission->chairman_id == $user->id;
        if (!$user->hasAdminPrivileges() && !$isChairman) {
            return response()->json([
                'success' => false,
                'message' => 'Iba predseda komisie môže overovať inventarizáciu.'
            ], 403);
        }

        // Kontrola či je položka v správnom stave
        if ($item->assignment_status != InventoryPlanItem::ASSIGNMENT_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Položka musí byť najprv dokončená pred overením.'
            ], 422);
        }

        try {
            $item->update([
                'assignment_status' => InventoryPlanItem::ASSIGNMENT_VERIFIED,
                'verified_at' => now(),
                'verified_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inventarizácia položky bola overená.',
                'item' => $item->fresh(['asset'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri overovaní: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resetovanie statusu položky (iba predseda komisie)
     */
    public function resetItem(Request $request, InventoryPlanItem $item)
    {
        $user = auth()->user();
        
        // Kontrola oprávnení - iba predseda komisie alebo admin
        $isChairman = $item->plan->commission && $item->plan->commission->chairman_id == $user->id;
        if (!$user->hasAdminPrivileges() && !$isChairman) {
            return response()->json([
                'success' => false,
                'message' => 'Iba predseda komisie môže resetovať inventarizáciu.'
            ], 403);
        }

        try {
            // Vymazanie všetkých záznamov o počte pre túto položku
            $item->counts()->delete();

            // Reset statusu položky
            $item->update([
                'assignment_status' => InventoryPlanItem::ASSIGNMENT_ASSIGNED,
                'started_at' => null,
                'started_by' => null,
                'completed_at' => null,
                'completed_by' => null,
                'verified_at' => null,
                'verified_by' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status položky bol resetovaný.',
                'item' => $item->fresh(['asset'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri resetovaní: ' . $e->getMessage()
            ], 500);
        }
    }
}