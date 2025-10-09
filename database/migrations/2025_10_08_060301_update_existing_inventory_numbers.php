<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateExistingInventoryNumbers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Aktualizujeme existujúce inventárne čísla na nový formát
        $assets = DB::table('assets')
                    ->whereNotNull('acquisition_date')
                    ->get();
        
        foreach ($assets as $asset) {
            // Zálohujeme pôvodné inventárne číslo do metadata
            $metadata = $asset->metadata ? json_decode($asset->metadata, true) : [];
            $metadata['original_inventory_number'] = $asset->inventory_number;
            
            // Vygenerujeme nové inventárne číslo vo formáte ROK-ID
            $acquisitionYear = date('Y', strtotime($asset->acquisition_date));
            $newInventoryNumber = sprintf('%d-%d', $acquisitionYear, $asset->id);
            
            // Aktualizujeme záznam
            DB::table('assets')
                ->where('id', $asset->id)
                ->update([
                    'inventory_number' => $newInventoryNumber,
                    'metadata' => json_encode($metadata),
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Obnovíme pôvodné inventárne čísla z metadata
        $assets = DB::table('assets')
                    ->whereNotNull('metadata')
                    ->get();
        
        foreach ($assets as $asset) {
            $metadata = $asset->metadata ? json_decode($asset->metadata, true) : [];
            
            if (isset($metadata['original_inventory_number'])) {
                $originalInventoryNumber = $metadata['original_inventory_number'];
                
                // Odstránime zálohu z metadata
                unset($metadata['original_inventory_number']);
                $metadataJson = empty($metadata) ? null : json_encode($metadata);
                
                // Obnovíme pôvodné inventárne číslo
                DB::table('assets')
                    ->where('id', $asset->id)
                    ->update([
                        'inventory_number' => $originalInventoryNumber,
                        'metadata' => $metadataJson,
                        'updated_at' => now()
                    ]);
            }
        }
    }
}
