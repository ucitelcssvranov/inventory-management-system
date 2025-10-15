<?php
// Návrh integračných funkcií pre asset management

/**
 * 1. Email notifikácie
 */
class AssetNotificationService 
{
    public function sendWarrantyExpiryNotifications()
    {
        $expiringAssets = Asset::whereDate('warranty_end', '<=', now()->addDays(30))->get();
        
        foreach($expiringAssets as $asset) {
            Mail::to($asset->owner_email)->send(new WarrantyExpiryNotification($asset));
        }
    }
    
    public function sendMaintenanceReminders()
    {
        $assetsDue = Asset::whereDate('next_maintenance', '<=', now())->get();
        // Odoslať notifikácie
    }
}

/**
 * 2. Import/Export funkcionalita
 */
class AssetImportExportService
{
    public function importFromCsv($filePath)
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        
        foreach($csv as $record) {
            Asset::create([
                'name' => $record['name'],
                'serial_number' => $record['serial_number'],
                'acquisition_cost' => $record['cost'],
                // ... mapping ostatných polí
            ]);
        }
    }
    
    public function exportToPdf($assetIds = null)
    {
        $assets = $assetIds ? Asset::whereIn('id', $assetIds)->get() : Asset::all();
        
        $pdf = PDF::loadView('assets.export-pdf', compact('assets'));
        return $pdf->download('assets-export.pdf');
    }
}

/**
 * 3. API integrácie
 */
class AssetApiIntegrationService
{
    // Integrácia s externými systémami
    public function syncWithAccountingSystem()
    {
        // Synchronizácia s účtovným systémom
    }
    
    public function syncWithMaintenanceSystem()
    {
        // Integrácia so systémom údržby
    }
}

/**
 * 4. Backup a archivovanie
 */
class AssetArchiveService
{
    public function archiveOldAssets()
    {
        $oldAssets = Asset::where('status', 'written_off')
                         ->whereDate('updated_at', '<', now()->subYears(5))
                         ->get();
        
        // Archivovať do separátnej tabuľky
        foreach($oldAssets as $asset) {
            ArchivedAsset::create($asset->toArray());
            $asset->delete();
        }
    }
}