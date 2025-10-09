<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\Category;
use App\Models\InventoryCommission;
use App\Models\Location;
use Carbon\Carbon;

class ImportAssetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Najprv vytvoríme potrebné kategórie a komisie ak neexistujú
        $hardwareCategory = Category::firstOrCreate([
            'name' => 'Hardware'
        ], [
            'description' => 'Hardvérové zariadenia'
        ]);

        $informatikaCommission = InventoryCommission::firstOrCreate([
            'name' => 'Informatika'
        ], [
            'description' => 'Inventarizačná komisia pre informatiku'
        ]);

        // Náhodná lokácia (môžete upraviť podľa potreby)
        $defaultLocation = Location::first();

        // Údaje majetku na import
        $assetsData = [
            [
                'name' => 'Notebook Lenovo ThinkBook 16 G7 ARP Touch',
                'count' => 3,
                'acquisition_cost' => 839.11,
                'commission' => 'Informatika',
                'serial_numbers' => ['PWOKGQHZ', 'PVVOKGQLM', 'PWOKGQJS'],
                'category' => 'Hardware',
                'status' => 'active'
            ],
            [
                'name' => 'Základný balík inkluzívneho vybavenia pre Samsung Galaxy Tab S9, digital pen + školu - Tablet s oddeliteľnou klávesnicou a keyboard SK, adaptér stylusom (dotykové pero)',
                'count' => 3,
                'acquisition_cost' => 836.40,
                'commission' => 'Informatika',
                'serial_numbers' => ['R52Y50FHAJN', 'R52Y50FH7AB', 'R52Y50FHBPR'],
                'category' => 'Hardware',
                'status' => 'active'
            ],
            [
                'name' => 'Brother MFC-L8690CDW',
                'count' => 3,
                'acquisition_cost' => 392.37,
                'commission' => 'Informatika',
                'serial_numbers' => ['E77443C5F479174', 'E77443C5F479183', 'E77443C5F479175'],
                'category' => 'Hardware',
                'status' => 'active'
            ],
            [
                'name' => 'Projektor BENQ LW600ST+',
                'count' => 3,
                'acquisition_cost' => 744.15,
                'commission' => 'Informatika',
                'serial_numbers' => ['PBA5S01290000', 'PBA5S01289000', 'PBA5S01316000'],
                'category' => 'Hardware',
                'status' => 'active'
            ],
            [
                'name' => 'Monitor Viewsonic VG2409U-2',
                'count' => 3,
                'acquisition_cost' => 253.38,
                'commission' => 'Informatika',
                'serial_numbers' => ['XT8251100425', 'XT8251100373', 'XT8251100421'],
                'category' => 'Hardware',
                'status' => 'active'
            ],
            [
                'name' => 'LENOVO ideapad Duet 3+keyboard SK + digital pen',
                'count' => 21,
                'acquisition_cost' => 644.52,
                'commission' => 'Informatika',
                'serial_numbers' => [],
                'category' => 'Hardware',
                'status' => 'active'
            ],
            [
                'name' => 'Dell Pro Tower + Monitor Dell 24" P2424HEB',
                'count' => 12,
                'acquisition_cost' => 982.77,
                'commission' => 'Informatika',
                'serial_numbers' => [],
                'category' => 'Hardware',
                'status' => 'active'
            ],
            [
                'name' => 'OKI MC883dn + podávač + podstavec',
                'count' => 1,
                'acquisition_cost' => 1867.14,
                'commission' => 'Informatika',
                'serial_numbers' => ['BW46023256'],
                'category' => 'Hardware',
                'status' => 'active'
            ],
            [
                'name' => 'SVEN reproduktory',
                'count' => 4,
                'acquisition_cost' => 79.95,
                'commission' => 'Informatika',
                'serial_numbers' => [],
                'category' => 'Hardware',
                'status' => 'active'
            ]
        ];

        // Spracovanie a vytvorenie majetku
        foreach ($assetsData as $assetData) {
            for ($i = 0; $i < $assetData['count']; $i++) {
                // Ak má zariadenie sériové čísla, priradíme konkrétne číslo
                $serialNumber = null;
                if (!empty($assetData['serial_numbers']) && isset($assetData['serial_numbers'][$i])) {
                    $serialNumber = $assetData['serial_numbers'][$i];
                }

                // Vytvoríme názov - ak je viac zariadení, pridáme číslo
                $name = $assetData['name'];
                if ($assetData['count'] > 1) {
                    $name .= ' #' . ($i + 1);
                }

                Asset::create([
                    'name' => $name,
                    'serial_number' => $serialNumber,
                    'category_id' => $hardwareCategory->id,
                    'location_id' => $defaultLocation ? $defaultLocation->id : null,
                    'acquisition_date' => Carbon::now()->subDays(rand(1, 365)), // Náhodný dátum za posledný rok
                    'acquisition_cost' => $assetData['acquisition_cost'],
                    'residual_value' => $assetData['acquisition_cost'] * 0.8, // 80% z obstarávacej ceny
                    'status' => $assetData['status'],
                    'inventory_commission' => $assetData['commission'],
                    'owner' => 'CSŠ Vranov nad Topľou',
                    'description' => 'Importované zariadenie',
                    'created_by' => 1, // Admin user
                    'inventory_number' => '', // Bude vygenerované automaticky
                ]);
            }
        }

        $this->command->info('Úspešne importovaných ' . collect($assetsData)->sum('count') . ' položiek majetku.');
    }
}
