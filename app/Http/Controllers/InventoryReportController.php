<?php

namespace App\Http\Controllers;

use App\Models\InventoryPlan;

class InventoryReportController extends Controller
{
    public function index()
    {
        $plans = InventoryPlan::with('createdBy', 'responsiblePerson')->orderByDesc('created_at')->get();
        return view('inventory_reports.index', compact('plans'));
    }
}
