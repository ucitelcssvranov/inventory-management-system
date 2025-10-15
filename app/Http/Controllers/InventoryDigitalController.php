<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryPlanItem;
use App\Models\InventoryCount;
use Carbon\Carbon;

class InventoryDigitalController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Start inventory for a specific item
     */
    public function startItemInventory(Request $request, InventoryPlanItem $planItem)
    {
        // Check if user is authorized to work on this item
        $user = auth()->user();
        if (!$planItem->canUserWork($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Update the assignment status
        $planItem->update([
            'assignment_status' => InventoryPlanItem::ASSIGNMENT_IN_PROGRESS,
            'started_at' => Carbon::now(),
            'assigned_user_id' => $user->id
        ]);

        return response()->json(['success' => true, 'message' => 'Inventory started']);
    }

    /**
     * Save count for a specific item
     */
    public function saveItemCount(Request $request, InventoryPlanItem $planItem)
    {
        $request->validate([
            'physical_count' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        $user = auth()->user();
        if (!$planItem->canUserWork($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Create or update the inventory count
        $count = InventoryCount::updateOrCreate(
            [
                'inventory_plan_item_id' => $planItem->id,
                'user_id' => $user->id
            ],
            [
                'physical_count' => $request->physical_count,
                'notes' => $request->notes,
                'counted_at' => Carbon::now()
            ]
        );

        return response()->json(['success' => true, 'message' => 'Count saved']);
    }

    /**
     * Complete inventory for a specific item
     */
    public function completeItemInventory(Request $request, InventoryPlanItem $planItem)
    {
        $request->validate([
            'physical_count' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        $user = auth()->user();
        if (!$planItem->canUserWork($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Save the final count
        $count = InventoryCount::updateOrCreate(
            [
                'inventory_plan_item_id' => $planItem->id,
                'user_id' => $user->id
            ],
            [
                'physical_count' => $request->physical_count,
                'notes' => $request->notes,
                'counted_at' => Carbon::now()
            ]
        );

        // Mark the item as completed
        $planItem->update([
            'assignment_status' => InventoryPlanItem::ASSIGNMENT_COMPLETED,
            'completed_at' => Carbon::now(),
            'assigned_user_id' => $user->id
        ]);

        return response()->json(['success' => true, 'message' => 'Inventory completed']);
    }
}