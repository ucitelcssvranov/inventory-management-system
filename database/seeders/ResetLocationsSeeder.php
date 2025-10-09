<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\User;

class ResetLocationsSeeder extends Seeder
{
    public function run()
    {
        // Získaj prvého admin používateľa (pre created_by)
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::first(); // Ak nie je admin, vezmi prvého používateľa
        }
        
        if (!$admin) {
            $this->command->error('Nenašiel sa žiadny používateľ v databáze!');
            return;
        }

        // Vymaž všetky existujúce lokácie
        $this->command->info('Vymazávam všetky existujúce budovy a miestnosti...');
        Location::query()->delete();

        // Vytvor novú budovu "Stará budova"
        $this->command->info('Vytváram budovu "Stará budova"...');
        $staraBudova = Location::create([
            'name' => 'Stará budova',
            'type' => 'budova',
            'parent_id' => null,
            'description' => 'Hlavná budova školy',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Zoznam miestností s číslami a popismi
        $miestnosti = [
            ['number' => '101', 'description' => 'Riaditeľňa'],
            ['number' => '102', 'description' => 'Ekonóm'],
            ['number' => '103', 'description' => 'Sakristia'],
            ['number' => '105', 'description' => 'Bufet1'],
            ['number' => '106', 'description' => 'Bufet2'],
            ['number' => '109', 'description' => 'Zástupca'],
            ['number' => '110', 'description' => 'Zborovňa'],
            ['number' => '111', 'description' => 'Kaviareň'],
            ['number' => '209', 'description' => 'Trieda'],
            ['number' => '210', 'description' => 'Trieda'],
            ['number' => '211', 'description' => 'Trieda'],
            ['number' => '212', 'description' => 'Trieda'],
            ['number' => '213', 'description' => 'Sklad učebných pomôcok'],
            ['number' => '201', 'description' => 'Sklad učebných pomôcok'],
            ['number' => '202', 'description' => 'Trieda'],
            ['number' => '203', 'description' => 'Trieda'],
            ['number' => '204', 'description' => 'Trieda'],
            ['number' => '208', 'description' => 'B/CH/F'],
            ['number' => '207', 'description' => 'B/CH/F Kabinet'],
            ['number' => '205', 'description' => 'B/CH/F Učebňa'],
            ['number' => '206', 'description' => 'B/CH/F Učebňa 2'],
        ];

        // Vytvor miestnosti
        $this->command->info('Vytváram miestnosti...');
        foreach ($miestnosti as $miestnost) {
            // Názov miestnosti je kombinácia čísla a popisu
            $nazov = $miestnost['number'] . ' ' . $miestnost['description'];
            
            Location::create([
                'parent_id' => $staraBudova->id,
                'type' => 'miestnost',
                'room_number' => $miestnost['number'],
                'room_description' => $miestnost['description'],
                'name' => $nazov, // Pre kompatibilitu s existujúcim kódom
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]);
            
            $this->command->info("Vytvorená miestnosť: {$nazov}");
        }

        $this->command->info('Úspešne vytvorená budova "Stará budova" s ' . count($miestnosti) . ' miestnosťami!');
    }
}