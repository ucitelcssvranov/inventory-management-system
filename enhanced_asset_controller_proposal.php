<?php
// Návrh vylepšeného AssetController s pokročilými funkciami

class EnhancedAssetController extends Controller 
{
    /**
     * Dashboard pre asset management
     */
    public function dashboard()
    {
        $stats = [
            'total_assets' => Asset::count(),
            'total_value' => Asset::sum('acquisition_cost'),
            'assets_by_status' => Asset::groupBy('status')->selectRaw('status, count(*) as count')->get(),
            'assets_by_category' => Asset::with('category')->get()->groupBy('category.name'),
            'warranty_expiring' => Asset::whereDate('warranty_end', '<=', now()->addDays(30))->count(),
            'maintenance_due' => Asset::whereDate('next_maintenance', '<=', now())->count(),
            'top_locations' => Asset::with('location')->get()->groupBy('location.name')->map->count()->sortDesc()->take(5),
        ];
        
        return view('assets.dashboard', compact('stats'));
    }
    
    /**
     * Bulk operations
     */
    public function bulkUpdate(Request $request)
    {
        $assetIds = $request->input('asset_ids');
        $operation = $request->input('operation');
        
        switch($operation) {
            case 'change_status':
                Asset::whereIn('id', $assetIds)->update(['status' => $request->input('new_status')]);
                break;
            case 'change_location':
                Asset::whereIn('id', $assetIds)->update(['location_id' => $request->input('new_location_id')]);
                break;
            case 'assign_owner':
                Asset::whereIn('id', $assetIds)->update(['owner' => $request->input('new_owner')]);
                break;
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Generate QR codes
     */
    public function generateQrCodes(Request $request)
    {
        $assetIds = $request->input('asset_ids');
        $assets = Asset::whereIn('id', $assetIds)->get();
        
        $qrCodes = [];
        foreach($assets as $asset) {
            $qrCodes[] = [
                'id' => $asset->id,
                'name' => $asset->name,
                'inventory_number' => $asset->inventory_number,
                'qr_code' => $this->generateQrCodeImage($asset->inventory_number)
            ];
        }
        
        return view('assets.qr-codes-print', compact('qrCodes'));
    }
    
    /**
     * Asset history/audit trail
     */
    public function history(Asset $asset)
    {
        $history = $asset->audits()->with('user')->orderBy('created_at', 'desc')->get();
        return view('assets.history', compact('asset', 'history'));
    }
    
    /**
     * Maintenance scheduling
     */
    public function scheduleMaintenance(Asset $asset, Request $request)
    {
        $validated = $request->validate([
            'maintenance_type' => 'required|string',
            'scheduled_date' => 'required|date',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id'
        ]);
        
        $asset->maintenanceSchedules()->create($validated);
        
        return redirect()->back()->with('success', 'Údržba naplánovaná');
    }
}