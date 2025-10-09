<?php

namespace App\Http\Controllers;

use App\Models\InventoryCommission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class InventoryCommissionController extends Controller
{
    public function index()
    {
        $query = InventoryCommission::with(['chairman', 'members', 'createdBy', 'updatedBy'])
            ->withCount(['members', 'inventoryPlans']);

        // Ak nie je admin, zobraz len komisie kde je používateľ členom alebo predsedom
        if (!auth()->user()->isAdmin()) {
            $userId = auth()->id();
            $query->where(function($q) use ($userId) {
                $q->where('chairman_id', $userId)
                  ->orWhereHas('members', function($subQ) use ($userId) {
                      $subQ->where('user_id', $userId);
                  });
            });
        }
        
        $commissions = $query->orderBy('name')->paginate(15);
        
        return view('inventory-commissions.index', compact('commissions'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('inventory-commissions.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(InventoryCommission::validationRules());
        $validated['created_by'] = auth()->id();
        
        $commission = InventoryCommission::create($validated);
        
        // Pridanie členov (ak existujú)
        if ($request->has('members') && is_array($request->members)) {
            $commission->members()->attach($request->members);
        }
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Inventarizačná komisia bola úspešne vytvorená.',
                'commission' => $commission->fresh(['chairman', 'members', 'createdBy', 'updatedBy'])
            ]);
        }
        
        return redirect()->route('inventory-commissions.index')
            ->with('success', 'Inventarizačná komisia bola úspešne vytvorená.');
    }

    public function show(InventoryCommission $inventoryCommission)
    {
        // Kontrola prístupu - len admin alebo členovia/predseda komisie
        if (!auth()->user()->isAdmin() && !$this->userCanAccessCommission($inventoryCommission)) {
            abort(403, 'Nemáte oprávnenie na zobrazenie tejto komisie.');
        }

        $inventoryCommission->load([
            'chairman', 
            'members', 
            'inventoryPlans.items', 
            'createdBy', 
            'updatedBy'
        ]);
        
        return view('inventory-commissions.show', compact('inventoryCommission'));
    }

    public function edit(InventoryCommission $inventoryCommission)
    {
        // Kontrola prístupu - len admin alebo predseda komisie
        if (!auth()->user()->isAdmin() && auth()->id() !== $inventoryCommission->chairman_id) {
            abort(403, 'Nemáte oprávnenie na úpravu tejto komisie.');
        }

        $users = User::orderBy('name')->get();
        $inventoryCommission->load(['chairman', 'members']);
        
        return view('inventory-commissions.edit', compact('inventoryCommission', 'users'));
    }

    public function update(Request $request, InventoryCommission $inventoryCommission)
    {
        // Kontrola prístupu - len admin alebo predseda komisie
        if (!auth()->user()->isAdmin() && auth()->id() !== $inventoryCommission->chairman_id) {
            abort(403, 'Nemáte oprávnenie na úpravu tejto komisie.');
        }

        $validated = $request->validate(InventoryCommission::validationRules($inventoryCommission->id));
        $validated['updated_by'] = auth()->id();
        
        $inventoryCommission->update($validated);
        
        // Aktualizácia členov
        if ($request->has('members')) {
            $members = is_array($request->members) ? $request->members : [];
            $inventoryCommission->members()->sync($members);
        } else {
            $inventoryCommission->members()->detach();
        }
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Inventarizačná komisia bola úspešne upravená.',
                'commission' => $inventoryCommission->fresh(['chairman', 'members', 'createdBy', 'updatedBy'])
            ]);
        }
        
        return redirect()->route('inventory-commissions.index')
            ->with('success', 'Inventarizačná komisia bola úspešne upravená.');
    }

    public function destroy(InventoryCommission $inventoryCommission)
    {
        // Kontrola, či komisia nemá pridelené inventarizačné plány
        if ($inventoryCommission->inventoryPlans()->count() > 0) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nie je možné vymazať komisiu, ktorá má pridelené inventarizačné plány.'
                ], 422);
            }
            return redirect()->route('inventory-commissions.index')
                ->with('error', 'Nie je možné vymazať komisiu, ktorá má pridelené inventarizačné plány.');
        }
        
        $commissionName = $inventoryCommission->name;
        $inventoryCommission->delete();
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Inventarizačná komisia '{$commissionName}' bola úspešne vymazaná."
            ]);
        }
        
        return redirect()->route('inventory-commissions.index')
            ->with('success', "Inventarizačná komisia '{$commissionName}' bola úspešne vymazaná.");
    }

    /**
     * AJAX metóda pre rýchlu inline editáciu
     */
    public function quickUpdate(Request $request, InventoryCommission $inventoryCommission)
    {
        $rules = [
            'url' => 'nullable|url',
            'description' => 'nullable|string|max:1000',
        ];

        $validated = $request->validate($rules);
        $validated['updated_by'] = auth()->id();
        
        $inventoryCommission->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inventarizačná komisia bola úspešne upravená.',
            'commission' => $inventoryCommission->fresh(['chairman', 'members', 'createdBy', 'updatedBy'])
        ]);
    }

    /**
     * AJAX metóda pre aktualizáciu členov komisie
     */
    public function updateMembers(Request $request, InventoryCommission $inventoryCommission)
    {
        $validated = $request->validate([
            'chairman_id' => 'required|exists:users,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id|different:chairman_id',
        ]);

        $inventoryCommission->update([
            'chairman_id' => $validated['chairman_id'],
            'updated_by' => auth()->id()
        ]);

        // Aktualizácia členov
        $members = $validated['members'] ?? [];
        $inventoryCommission->members()->sync($members);

        return response()->json([
            'success' => true,
            'message' => 'Členovia komisie boli úspešne aktualizovaní.',
            'commission' => $inventoryCommission->fresh(['chairman', 'members'])
        ]);
    }

    /**
     * Dashboard pre inventarizačné komisie
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Získať komisie používateľa
        $userCommissions = $user->allCommissions();
        
        // Ak nie je členom žiadnej komisie a nie je admin, presmerovať
        if ($userCommissions->isEmpty() && !$user->isAdmin() && !$user->isInventoryManager()) {
            return redirect()->route('home')
                ->with('warning', 'Nie ste členom žiadnej inventarizačnej komisie.');
        }

        // Pre admina a správcu inventarizácie získať všetky komisie
        if ($user->isAdmin() || $user->isInventoryManager()) {
            $commissions = InventoryCommission::with([
                'chairman',
                'members',
                'inventoryPlans.inventoryGroups',
                'inventoryGroups'
            ])->get();
        } else {
            $commissions = $userCommissions->load([
                'chairman',
                'members',
                'inventoryPlans.inventoryGroups',
                'inventoryGroups'
            ]);
        }

        // Štatistiky pre každú komisiu
        $commissionStats = [];
        foreach ($commissions as $commission) {
            $groups = $commission->inventoryGroups;
            
            $commissionStats[$commission->id] = [
                'total_groups' => $groups->count(),
                'active_groups' => $groups->whereIn('status', [
                    \App\Models\InventoryGroup::STATUS_ASSIGNED,
                    \App\Models\InventoryGroup::STATUS_IN_PROGRESS
                ])->count(),
                'completed_groups' => $groups->where('status', \App\Models\InventoryGroup::STATUS_COMPLETED)->count(),
                'total_plans' => $commission->inventoryPlans()->count(),
                'active_plans' => $commission->inventoryPlans()
                    ->whereIn('status', [
                        \App\Models\InventoryPlan::STATUS_ASSIGNED,
                        \App\Models\InventoryPlan::STATUS_IN_PROGRESS
                    ])->count()
            ];
        }

        // Globálne štatistiky
        $globalStats = [
            'my_commissions' => $userCommissions->count(),
            'my_leading_commissions' => $user->chairmanCommissions()->count(),
            'my_groups' => $user->allGroups()->count(),
            'my_leading_groups' => $user->leadingGroups()->count()
        ];

        // Najnovšie aktivity (pre všetky komisie používateľa)
        $recentActivities = [];
        if (!$userCommissions->isEmpty()) {
            $commissionIds = $userCommissions->pluck('id');
            
            $recentGroups = \App\Models\InventoryGroup::whereIn('commission_id', $commissionIds)
                ->with(['commission', 'leader', 'inventoryPlan'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();
                
            foreach ($recentGroups as $group) {
                $recentActivities[] = [
                    'type' => 'group_update',
                    'title' => "Skupina {$group->name} - {$group->status_label}",
                    'description' => "Komisia: {$group->commission->name}",
                    'timestamp' => $group->updated_at,
                    'url' => route('inventory-groups.show', $group)
                ];
            }
        }

        // Aktuálne úlohy pre používateľa
        $currentTasks = [];
        
        // Skupiny, kde je používateľ vedúci a sú aktívne
        $activeLeadingGroups = $user->leadingGroups()
            ->whereIn('status', [
                \App\Models\InventoryGroup::STATUS_ASSIGNED,
                \App\Models\InventoryGroup::STATUS_IN_PROGRESS
            ])
            ->with(['commission', 'inventoryPlan'])
            ->get();
            
        foreach ($activeLeadingGroups as $group) {
            $currentTasks[] = [
                'type' => 'group_leadership',
                'title' => "Vedujem skupinu: {$group->name}",
                'description' => "Status: {$group->status_label}, Pokrok: {$group->progress_percentage}%",
                'priority' => $group->status === \App\Models\InventoryGroup::STATUS_IN_PROGRESS ? 'high' : 'medium',
                'url' => route('inventory-groups.show', $group)
            ];
        }

        // Skupiny, kde je používateľ člen a sú aktívne
        $activeMemberGroups = $user->memberGroups()
            ->whereIn('status', [
                \App\Models\InventoryGroup::STATUS_ASSIGNED,
                \App\Models\InventoryGroup::STATUS_IN_PROGRESS
            ])
            ->with(['commission', 'inventoryPlan', 'leader'])
            ->get();
            
        foreach ($activeMemberGroups as $group) {
            $currentTasks[] = [
                'type' => 'group_membership',
                'title' => "Účasť v skupine: {$group->name}",
                'description' => "Vedúci: {$group->leader->name}, Pokrok: {$group->progress_percentage}%",
                'priority' => $group->status === \App\Models\InventoryGroup::STATUS_IN_PROGRESS ? 'high' : 'low',
                'url' => route('inventory-groups.show', $group)
            ];
        }

        // Zoradiť úlohy podľa priority
        usort($currentTasks, function($a, $b) {
            $priorities = ['high' => 3, 'medium' => 2, 'low' => 1];
            return ($priorities[$b['priority']] ?? 0) <=> ($priorities[$a['priority']] ?? 0);
        });

        return view('inventory-commissions.dashboard', compact(
            'commissions',
            'commissionStats',
            'globalStats',
            'recentActivities',
            'currentTasks'
        ));
    }

    /**
     * Dashboard pre konkrétnu komisiu
     */
    public function commissionDashboard(InventoryCommission $inventoryCommission)
    {
        $user = auth()->user();
        
        // Kontrola oprávnení
        if (!$user->isAdmin() && !$user->isInventoryManager() && !$user->isCommissionMember($inventoryCommission->id)) {
            return redirect()->route('inventory-commissions.dashboard')
                ->with('error', 'Nemáte oprávnenie pristupovať k tejto komisii.');
        }

        $inventoryCommission->load([
            'chairman',
            'members',
            'inventoryPlans.inventoryGroups.leader',
            'inventoryGroups.leader',
            'inventoryGroups.members',
            'inventoryGroups.planItems.asset'
        ]);

        // Detailné štatistiky komisie
        $stats = [
            'total_plans' => $inventoryCommission->inventoryPlans()->count(),
            'active_plans' => $inventoryCommission->inventoryPlans()
                ->whereIn('status', [
                    \App\Models\InventoryPlan::STATUS_ASSIGNED,
                    \App\Models\InventoryPlan::STATUS_IN_PROGRESS
                ])->count(),
            'total_groups' => $inventoryCommission->inventoryGroups()->count(),
            'active_groups' => $inventoryCommission->inventoryGroups()
                ->whereIn('status', [
                    \App\Models\InventoryGroup::STATUS_ASSIGNED,
                    \App\Models\InventoryGroup::STATUS_IN_PROGRESS
                ])->count(),
            'completed_groups' => $inventoryCommission->inventoryGroups()
                ->where('status', \App\Models\InventoryGroup::STATUS_COMPLETED)
                ->count(),
            'total_assets' => $inventoryCommission->inventoryGroups()
                ->with('planItems')
                ->get()
                ->sum(function($group) {
                    return $group->planItems->count();
                }),
            'completed_assets' => $inventoryCommission->inventoryGroups()
                ->with(['planItems' => function($query) {
                    $query->whereIn('assignment_status', [
                        \App\Models\InventoryPlanItem::ASSIGNMENT_COMPLETED,
                        \App\Models\InventoryPlanItem::ASSIGNMENT_VERIFIED
                    ]);
                }])
                ->get()
                ->sum(function($group) {
                    return $group->planItems->count();
                })
        ];

        // Pokrok jednotlivých skupín
        $groupProgress = $inventoryCommission->inventoryGroups()
            ->with(['leader', 'inventoryPlan'])
            ->orderBy('status')
            ->orderBy('name')
            ->get();

        return view('inventory-commissions.commission-dashboard', compact(
            'inventoryCommission',
            'stats',
            'groupProgress'
        ));
    }

    /**
     * Kontrola či má používateľ prístup k komisii
     */
    private function userCanAccessCommission(InventoryCommission $commission)
    {
        $userId = auth()->id();
        
        // Predseda komisie
        if ($commission->chairman_id == $userId) {
            return true;
        }
        
        // Člen komisie
        if ($commission->members()->where('user_id', $userId)->exists()) {
            return true;
        }
        
        return false;
    }
}
