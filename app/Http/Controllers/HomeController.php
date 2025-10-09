<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\InventoryPlan;
use App\Models\InventoryPlanItem;
use App\Models\InventoryCommission;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin vidí všetky štatistiky
            $stats = [
                'assets_count' => Asset::count(),
                'categories_count' => Category::count(),
                'locations_count' => Location::count(),
                'buildings_count' => Location::where('type', 'budova')->count(),
                'rooms_count' => Location::where('type', 'room')->count(),
                'inventory_plans_count' => InventoryPlan::count(),
                'active_inventory_plans' => InventoryPlan::where('status', 'active')->count(),
                'inventory_commission_count' => InventoryCommission::count(),
                'users_count' => User::count(),
                'recent_assets' => Asset::latest()->take(5)->get(),
                'recent_inventory_plans' => InventoryPlan::latest()->take(3)->get(),
            ];
        } else {
            // Bežní používatelia vidia len svoje relevantné dáta
            $userCommissions = $user->memberOfCommissions()->pluck('inventory_commissions.id');
            $chairmanCommissions = $user->chairedCommissions()->pluck('id');
            $allUserCommissions = $userCommissions->merge($chairmanCommissions)->unique();
            
            $stats = [
                'my_commissions_count' => $allUserCommissions->count(),
                'my_active_plans' => InventoryPlan::whereIn('commission_id', $allUserCommissions)
                    ->where('status', 'active')->count(),
                'my_assigned_items' => InventoryPlanItem::whereHas('commission', function($q) use ($user) {
                    $q->whereHas('members', function($subQ) use ($user) {
                        $subQ->where('user_id', $user->id);
                    })->orWhere('chairman_id', $user->id);
                })->whereHas('plan', function($q) {
                    $q->whereIn('status', ['active', 'in_progress']);
                })->count(),
                'my_completed_items' => InventoryPlanItem::whereHas('commission', function($q) use ($user) {
                    $q->whereHas('members', function($subQ) use ($user) {
                        $subQ->where('user_id', $user->id);
                    })->orWhere('chairman_id', $user->id);
                })->where('assignment_status', InventoryPlanItem::ASSIGNMENT_COMPLETED)->count(),
                'my_recent_commissions' => InventoryCommission::whereIn('id', $allUserCommissions)
                    ->latest()->take(3)->get(),
            ];
        }

        return view('home', compact('stats'));
    }
}
